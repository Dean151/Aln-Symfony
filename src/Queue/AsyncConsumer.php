<?php

namespace App\Queue;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

final class AsyncConsumer extends AbstractQueue
{
    private LoggerInterface $logger;

    private ?AMQPStreamConnection $connection = null;
    private ?AMQPChannel $channel = null;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function start(LoopInterface $loop, MessageDequeueInterface $messageDequeue): void
    {
        // FIXME: clean this with parameters
        $host = $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1';
        $port = $_ENV['RABBITMQ_PORT'] ?? 5672;
        $username = $_ENV['RABBITMQ_USERNAME'] ?? 'guest';
        $password = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';

        $this->connection = new AMQPStreamConnection($host, $port, $username, $password);
        $this->channel = $this->connection->channel();

        $this->channel->queue_declare(AbstractQueue::QUEUE_SOCKET, false, false, false, false);

        $callback = function (AMQPMessage $message) use ($messageDequeue) {
            $messageDequeue->dequeueMessage($message);
        };

        $this->channel->basic_consume(AbstractQueue::QUEUE_SOCKET, '', false, true, false, false, $callback);
        $loop->addPeriodicTimer(0.5, function () {
            $this->channel?->wait(null, true);
        });

        $this->logger->info("Started rabbitmq consumer on {$host}:{$port}");
    }

    public function shutdown(): void
    {
        if ($this->channel) {
            $this->channel->close();
            $this->channel = null;
        }
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
            $this->logger->info('Stopped rabbitmq consumer');
        }
    }
}
