<?php

namespace App\Queue;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class AbstractQueue
{
    public const QUEUE_SOCKET = 'aln_socket.queue';
    public const QUEUE_RESPONSE = 'aln_response.queue';

    protected function getQueueConnection(): AMQPStreamConnection
    {
        $host = $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1';
        $port = $_ENV['RABBITMQ_PORT'] ?? 5672;
        $user = $_ENV['RABBITMQ_USERNAME'] ?? 'guest';
        $password = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';

        return new AMQPStreamConnection($host, $port, $user, $password);
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
