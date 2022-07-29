<?php

declare(strict_types=1);

namespace App\Socket;

use App\Socket\Messages\ChangeDefaultMealMessage;
use App\Socket\Messages\ChangePlanningMessage;
use App\Socket\Messages\EmptyFeederMessage;
use App\Socket\Messages\ExpectableMessageInterface;
use App\Socket\Messages\ExpectationMessage;
use App\Socket\Messages\FeedNowMessage;
use App\Socket\Messages\IdentificationMessage;
use App\Socket\Messages\MessageInterface;
use App\Socket\Messages\TimeMessage;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

use function Safe\hex2bin;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

final class FeederSimulator
{
    public const OPTION_NONE = 0b000;
    public const OPTION_EMPTY = 0b001;
    public const OPTION_UNRESPONSIVE = 0b010;
    public const OPTION_FAST_RESPONSE = 0b100;

    private ContainerBagInterface $params;
    private LoggerInterface $logger;

    private string $identifier = '';
    private int $options = self::OPTION_NONE;

    private ?LoopInterface $loop = null;
    private ?ConnectionInterface $connection = null;

    public function __construct(ContainerBagInterface $params, LoggerInterface $logger)
    {
        $this->params = $params;
        $this->logger = $logger;
    }

    /**
     * @param-stan int-mask-of<self::OPTIONS_*> $options
     */
    public function start(LoopInterface $loop, string $identifier, int $options = self::OPTION_NONE): void
    {
        if (12 != strlen($identifier)) {
            throw new InvalidArgumentException('identifier must be 12 characters long');
        }
        $this->loop = $loop;
        $this->identifier = $identifier;
        $this->options = $options;

        $host = $this->params->get('simulator.host');
        $port = $this->params->get('simulator.port');

        $uri = "{$host}:{$port}";
        $this->logger->info("Starting feeder simulator on {$host}:{$port}");

        $connector = new Connector([], $loop);
        $connector->connect($uri)->then($this->onConnected(...), $this->onConnectionError(...));
    }

    public function shutdown(): void
    {
        if ($this->connection) {
            $connection = $this->connection;
            $this->connection = null;
            $connection->close();
            $this->logger->info('Stopped feeder simulator');
        }
        $this->loop?->stop();
        $this->loop = null;
    }

    /**
     * @param-stan self::OPTION_* $option
     */
    private function hasOption(int $option): bool
    {
        return ($this->options & $option) === $option;
    }

    private function onConnected(ConnectionInterface $connection): void
    {
        $this->logger->info('Connected to websocket server');
        $this->connection = $connection;
        $connection->on('close', $this->onConnectionClosed(...));
        $connection->on('data', $this->onData(...));

        $this->sendIdentification(); // Send right away
        $this->loop?->addPeriodicTimer(10, $this->sendIdentification(...)); // Simulate identification call every 10s
    }

    private function onData(string $data): void
    {
        $hexadecimal = bin2hex($data);
        try {
            $message = MessageIdentification::identifyOutgoingMessage($hexadecimal);
            $this->logIncoming($message);
            if ($message instanceof ExpectableMessageInterface) {
                $this->sendExpectationIfResponsive($message->expectationMessage($this->identifier));
            }
            if ($message instanceof FeedNowMessage) {
                $this->sendAlertIfEmpty($message->getMealAmount());
            }
        } catch (\RuntimeException $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
        }
    }

    private function onConnectionError(\Exception $e): void
    {
        $this->logger->error($e->getMessage(), ['exception' => $e]);
        $this->shutdown();
    }

    private function onConnectionClosed(?int $code, ?string $reason): void
    {
        $this->logger->info("Connection closed: {$code} {$reason}");
        $this->shutdown();
    }

    private function sendMessage(MessageInterface $message): void
    {
        if (!$this->connection) {
            $this->logger->warning("No connection to send data {$message->hexadecimal()}");

            return;
        }
        if ($this->connection->write(hex2bin($message->hexadecimal()))) {
            $this->logger->info("Sent data {$message->hexadecimal()}");
        } else {
            $this->logger->warning("Failed sending data {$message->hexadecimal()}");
        }
    }

    private function sendDelayedMessage(float $interval, MessageInterface $message): void
    {
        if (!$this->loop) {
            $this->logger->warning("No loop to delay message {$message->hexadecimal()}");

            return;
        }
        $this->loop->addTimer($interval, function () use ($message) {
            $this->sendMessage($message);
        });
    }

    private function sendIdentification(): void
    {
        $this->sendMessage(new IdentificationMessage($this->identifier));
    }

    /**
     * @param int<5, 150> $mealAmount
     */
    private function sendAlertIfEmpty(int $mealAmount): void
    {
        if (!$this->hasOption(self::OPTION_EMPTY)) {
            return;
        }
        $interval = $this->hasOption(self::OPTION_FAST_RESPONSE) ? 0.1 : 5;
        $this->sendDelayedMessage($interval, new EmptyFeederMessage($this->identifier, $mealAmount));
    }

    private function sendExpectationIfResponsive(ExpectationMessage $message): void
    {
        if ($this->hasOption(self::OPTION_UNRESPONSIVE)) {
            return;
        }
        $interval = $this->hasOption(self::OPTION_FAST_RESPONSE) ? 0.1 : 0.5;
        $this->sendDelayedMessage($interval, $message);
    }

    private function logIncoming(MessageInterface $message): void
    {
        $this->logger->debug("Received raw: {$message->hexadecimal()}");
        if ($message instanceof TimeMessage) {
            $minutes = str_pad((string) $message->getTime()->minutes, 2, '0', STR_PAD_LEFT);
            $this->logger->info("Received time: {$message->getTime()->hours}h{$minutes}");
        } elseif ($message instanceof ChangeDefaultMealMessage) {
            $this->logger->info("Changed default meal to {$message->getMealAmount()}g");
        } elseif ($message instanceof FeedNowMessage) {
            $this->logger->info("Meal distributed of {$message->getMealAmount()}g");
        } elseif ($message instanceof ChangePlanningMessage) {
            $this->logger->info("Planning changed with {$message->getCount()} meal(s)");
            foreach ($message->getMeals() as $meal) {
                $minutes = str_pad((string) $meal->time->minutes, 2, '0', STR_PAD_LEFT);
                $this->logger->info(" - {$meal->time->hours}h{$minutes} – {$meal->amount}g");
            }
        } else {
            $this->logger->warning("Other message received: {$message->hexadecimal()}");
        }
    }
}
