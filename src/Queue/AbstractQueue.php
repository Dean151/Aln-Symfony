<?php

namespace App\Queue;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class AbstractQueue
{
    public const QUEUE_SOCKET = 'aln_socket.queue';
    public const QUEUE_RESPONSE = 'aln_response.queue';

    protected function getHost(): string
    {
        return $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1';
    }

    protected function getPort(): int
    {
        return $_ENV['RABBITMQ_PORT'] ?? 5672;
    }

    protected function getQueueConnection(): AMQPStreamConnection
    {
        $user = $_ENV['RABBITMQ_USERNAME'] ?? 'guest';
        $password = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';

        return new AMQPStreamConnection($this->getHost(), $this->getPort(), $user, $password);
    }

    /**
     * @param-stan self::QUEUE* $queue
     */
    protected function publishInQueue(AMQPChannel $channel, string $queue, AMQPMessage $message): void
    {
        $channel->queue_declare($queue, false, false, false, false);
        $channel->basic_publish($message, '', $queue);
    }
}
