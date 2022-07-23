<?php

declare(strict_types=1);

namespace App\Queue;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

final class AsyncConsumer extends AbstractQueue
{
    private LoggerInterface $logger;

    private ?AMQPStreamConnection $connection = null;
    private ?AMQPChannel $channel = null;

    public function __construct(ContainerBagInterface $params, LoggerInterface $logger)
    {
        $this->logger = $logger;
        parent::__construct($params);
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

        $this->logger->info("Started rabbitmq consumer on {$this->getHost()}:{$this->getPort()}");
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
