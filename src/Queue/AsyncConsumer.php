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
        $amqpHost = $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1';
        $amqpPort = $_ENV['RABBITMQ_PORT'] ?? 5672;
        $amqpUser = $_ENV['RABBITMQ_USERNAME'] ?? 'guest';
        $amqpPassword = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';

        $this->connection = new AMQPStreamConnection($amqpHost, $amqpPort, $amqpUser, $amqpPassword);
        $this->channel = $this->connection->channel();

        $this->channel->queue_declare(AbstractQueue::QUEUE_SOCKET, false, false, false, false);

        $callback = function (AMQPMessage $message) use ($messageDequeue) {
            $messageDequeue->dequeueMessage($message);
        };

        $this->channel->basic_consume(AbstractQueue::QUEUE_SOCKET, '', false, true, false, false, $callback);
        $loop->addPeriodicTimer(0.5, function () {
            $this->channel?->wait(null, true);
        });

        $this->logger->info("Started rabbitmq consumer on {$amqpHost}:{$amqpPort}");
    }

    public function shutdown(): void
    {
        $this->channel?->close();
        $this->connection?->close();
    }
}
