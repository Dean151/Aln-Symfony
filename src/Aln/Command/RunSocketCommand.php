<?php

namespace App\Aln\Command;

use App\Aln\Queue\AbstractQueue;
use App\Aln\Queue\MessageDequeueInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'aln:socket:run',
    description: 'Run the rabbitmq client & websocket server',
    hidden: false
)]
final class RunSocketCommand extends Command implements SignalableCommandInterface
{
    private MessageDequeueInterface $communicator;
    protected static $defaultName = 'aln:socket:run';

    private ?AMQPStreamConnection $connection;
    private ?AMQPChannel $channel;
    private ?IoServer $server;

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

        $amqpHost = $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1';
        $amqpPort = $_ENV['RABBITMQ_PORT'] ?? 5672;
        $amqpUser = $_ENV['RABBITMQ_USERNAME'] ?? 'guest';
        $amqpPassword = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';
        $output->writeln("Starting rabbitmq client on {$amqpHost}:{$amqpPort}");

        $connection = new AMQPStreamConnection($amqpHost, $amqpPort, $amqpUser, $amqpPassword);
        $this->connection = $connection;
        $channel = $connection->channel();
        $this->channel = $channel;

        $channel->queue_declare(AbstractQueue::QUEUE_SOCKET, false, false, false, false);

        $callback = function (AMQPMessage $message) {
            $this->communicator->dequeueMessage($message);
        };

        $channel->basic_consume(AbstractQueue::QUEUE_SOCKET, '', false, true, false, false, $callback);
        $loop->addPeriodicTimer(0.5, function () use ($channel) {
            $channel->wait(null, true);
        });

        $wsHost = $_ENV['WEBSOCKET_HOST'] ?? '0.0.0.0';
        $wsPort = $_ENV['WEBSOCKET_PORT'] ?? 9999;
        $output->writeln("Starting Ratchet websocket server on {$wsHost}:{$wsPort}");

        $socketServer = new SocketServer($wsHost.':'.$wsPort, [], $loop);
        $this->server = new IoServer(new HttpServer(new WsServer($this->communicator)), $socketServer, $loop);
        $output->writeln('Server is running');

        $loop->run();

        return Command::SUCCESS;
    }

    /**
     * @return int[]
     */
    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal): void
    {
        $this->channel?->close();
        $this->connection?->close();
        $this->server?->socket->close();
    }
}
