<?php

namespace App\Aln\Command;

use App\Aln\Socket\Messages\ChangeDefaultMealMessage;
use App\Aln\Socket\Messages\ChangePlanningMessage;
use App\Aln\Socket\Messages\DefaultMealChangedMessage;
use App\Aln\Socket\Messages\EmptyFeederMessage;
use App\Aln\Socket\Messages\FeedNowMessage;
use App\Aln\Socket\Messages\IdentificationMessage;
use App\Aln\Socket\Messages\MealDistributedMessage;
use App\Aln\Socket\Messages\PlanningChangedMessage;
use App\Aln\Socket\Messages\TimeMessage;

use function Ratchet\Client\connect;

use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;

use function Safe\hex2bin;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'aln:feeder:simulate',
    description: 'Run a fake feeder to connect on the websocket',
    hidden: false
)]
class SimulateFeederCommand extends Command
{
    protected function configure(): void
    {
        $this->setHelp('Run a fake feeder to connect on the websocket');
        $this->setDescription('Run a fake feeder that behave just like a real feeder, and that will help to debug APIs.');
        $this->addArgument('identifier', InputArgument::OPTIONAL, 'The feeder identifier to simulate', 'ALE123456789');
        $this->addArgument('empty-feeder', InputArgument::OPTIONAL, 'Simulate when feeder is empty', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!class_exists(\Ratchet\Client\WebSocket::class)) {
            throw new \RuntimeException('Simulate feeder is only available when --dev dependencies are installed with composer.');
        }

        $identifier = $input->getArgument('identifier');
        if (12 != strlen($identifier)) {
            throw new InvalidArgumentException('identifier must be 12 characters long');
        }
        $feederIsEmpty = (bool) $input->getArgument('empty-feeder');

        $loop = Loop::get();

        $wsHost = $_ENV['WEBSOCKET_HOST'] ?? '127.0.0.1';
        $wsPort = $_ENV['WEBSOCKET_PORT'] ?? 9999;
        $output->writeln("Starting fake feeder on {$wsHost}:{$wsPort}");
        connect('ws://'.$wsHost.':'.$wsPort, [], [], $loop)->then(function (WebSocket $connection) use ($output, $loop, $identifier, $feederIsEmpty) {
            // Simulate identification call every 10s
            $identification = new IdentificationMessage($identifier);
            $this->send($connection, $identification->hexadecimal(), $output);
            $loop->addPeriodicTimer(10, function () use ($connection, $identification, $output) {
                $this->send($connection, $identification->hexadecimal(), $output);
            });
            $connection->on('message', function (MessageInterface $message) use ($connection, $output, $loop, $identifier, $feederIsEmpty) {
                $hexadecimal = bin2hex($message->getContents());
                $this->handleMessage($connection, $hexadecimal, $output, $loop, $identifier, $feederIsEmpty);
            });
            $connection->on('close', function ($code = null, $reason = null) use ($output, $loop) {
                $output->writeln("Connection closed: {$reason}\n");
                $loop->stop();
            });
        }, function ($e) use ($output, $loop) {
            $output->writeln($e->getMessage());
            $loop->stop();
        });

        return Command::SUCCESS;
    }

    private function send(WebSocket $connection, string $hexadecimal, OutputInterface $output): void
    {
        $bin = hex2bin($hexadecimal);
        $message = new Frame($bin, true, Frame::OP_BINARY);
        if ($connection->send($message)) {
            $output->writeln("Sended data $hexadecimal");
        } else {
            $output->writeln("Failed sending data $hexadecimal");
        }
    }

    private function handleMessage(WebSocket $connection, string $hexadecimal, OutputInterface $output, LoopInterface $loop, string $identifier, bool $feederIsEmpty): void
    {
        if (str_starts_with($hexadecimal, '9da10601')) {
            $message = TimeMessage::decodeFrom($hexadecimal);
            $time = $message->getTime();
            $output->writeln("Received time: {$time['hours']}h{$time['minutes']}");
        } elseif (str_starts_with($hexadecimal, '9da106c3')) {
            $message = ChangeDefaultMealMessage::decodeFrom($hexadecimal);
            $output->writeln("Changed default meal to {$message->getMealAmount()}g");
            $loop->addTimer(0.5, function () use ($identifier, $connection, $output) {
                $response = new DefaultMealChangedMessage($identifier);
                $this->send($connection, $response->hexadecimal(), $output);
            });
        } elseif (str_starts_with($hexadecimal, '9da12dc4')) {
            $message = ChangePlanningMessage::decodeFrom($hexadecimal);
            $output->writeln("Planning changed with {$message->getCount()} meal(s)");
            foreach ($message->getMeals() as $meal) {
                $output->writeln(" - {$meal['time']['hours']}h{$meal['time']['minutes']} – {$meal['amount']}g");
            }
            $loop->addTimer(0.5, function () use ($identifier, $connection, $output) {
                $response = new PlanningChangedMessage($identifier);
                $this->send($connection, $response->hexadecimal(), $output);
            });
        } elseif (str_starts_with($hexadecimal, '9da106a2')) {
            $message = FeedNowMessage::decodeFrom('9da106a2');
            $output->writeln("Meal distributed of {$message->getMealAmount()}g");
            $loop->addTimer(0.5, function () use ($identifier, $connection, $output) {
                $response = new MealDistributedMessage($identifier);
                $this->send($connection, $response->hexadecimal(), $output);
            });
            if ($feederIsEmpty) {
                $loop->addTimer(3, function () use ($identifier, $connection, $output, $message) {
                    $response = new EmptyFeederMessage($identifier, $message->getMealAmount());
                    $this->send($connection, $response->hexadecimal(), $output);
                });
            }
        } else {
            $output->writeln("Unknown data $hexadecimal");
        }
    }
}
