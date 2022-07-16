<?php

namespace App\Aln\Command;

use App\Aln\Socket\MessageDequeueInterface;
use Bunny\Async\Client;
use Bunny\Channel;
use Bunny\Message;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Promise\Promise;
use React\Socket\SocketServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'aln:socket:run',
    description: 'Run the rabbitmq client & websocket server',
    hidden: false
)]
final class RunSocketCommand extends Command
{
    private MessageDequeueInterface $communicator;
    protected static $defaultName = 'aln:socket:run';

    public function __construct(MessageDequeueInterface $communicator)
    {
        $this->communicator = $communicator;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Run the rabbitmq client & websocket server');
        $this->setDescription('Run a bunny rabbitmq client ; and a ratchet websocket server to communicate with the feeders');
    }

    /**
     * @return-stan Command::*
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $loop = Loop::get();

        $rabbitmqHost = $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1';
        $rabbitmqPort = $_ENV['RABBITMQ_PORT'] ?? 5672;
        $output->writeln("Starting bunny rabbitmq client on {$rabbitmqHost}:{$rabbitmqPort}");

        $queueClient = new Client($loop, [
            'host' => $rabbitmqHost,
            'port' => $rabbitmqPort,
            'vhost' => $_ENV['RABBITMQ_VHOST'] ?? '/',
            'user' => $_ENV['RABBITMQ_USERNAME'] ?? 'guest',
            'password' => $_ENV['RABBITMQ_PASSWORD'] ?? 'guest',
        ]);
        $connect = $queueClient->connect();
        $channel = $connect->then(function (Client $client) {
            return $client->channel();
        });
        $qos = $channel->then(function (Channel $channel) {
            $qos = $channel->qos(0, 5);
            assert($qos instanceof Promise);

            return $qos->then(function () use ($channel) {
                return $channel;
            });
        });
        $qos->then(function (Channel $channel) {
            $channel->consume(
                function (Message $message, Channel $channel, Client $client) {
                    $this->communicator->dequeueMessage($message);
                    $channel->ack($message);
                },
                $_ENV['RABBITMQ_ALN_QUEUE'] ?? 'aln'
            );
        });

        $wsHost = $_ENV['WEBSOCKET_HOST'] ?? '0.0.0.0';
        $wsPort = $_ENV['WEBSOCKET_PORT'] ?? 9999;
        $output->writeln("Starting Ratchet websocket server on {$wsHost}:{$wsPort}");

        $socketServer = new SocketServer($wsHost.':'.$wsPort, [], $loop);
        $ioServer = new IoServer(new HttpServer(new WsServer($this->communicator)), $socketServer, $loop);
        $output->writeln('Server is running');

        // Will run the underlying loop
        $ioServer->run();

        return Command::SUCCESS;
    }
}
