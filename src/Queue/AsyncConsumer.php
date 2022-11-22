<?php

declare(strict_types=1);

namespace App\Queue;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class AsyncConsumer extends AbstractQueue
{
    private ?AMQPStreamConnection $connection = null;
    private ?AMQPChannel $channel = null;

    public function __construct(
        #[Autowire('%env(string:RABBITMQ_HOST)%')]
        string $host,
        #[Autowire('%env(int:RABBITMQ_PORT)%')]
        int $port,
        #[Autowire('%env(string:RABBITMQ_USERNAME)%')]
        string $username,
        #[Autowire('%env(string:RABBITMQ_PASSWORD)%')]
        string $password,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($host, $port, $username, $password);
    }

    public function start(LoopInterface $loop, MessageDequeueInterface $messageDequeue): void
    {
        $this->connection = $this->getQueueConnection();
        $this->channel = $this->connection->channel();

        $this->channel->queue_declare(AbstractQueue::QUEUE_SOCKET, false, false, false, false);

        $callback = function (AMQPMessage $message) use ($messageDequeue) {
            $messageDequeue->dequeueMessage($message);
        };

        $this->channel->basic_consume(AbstractQueue::QUEUE_SOCKET, '', false, true, false, false, $callback);
        $loop->addPeriodicTimer(0.5, function () {
            $this->channel?->wait(null, true);
        });

        $this->logger->info("Started rabbitmq consumer on {$this->host}:{$this->port}");
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
