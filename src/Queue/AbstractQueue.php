<?php

declare(strict_types=1);

namespace App\Queue;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

abstract class AbstractQueue
{
    public const QUEUE_SOCKET = 'aln_socket.queue';
    public const QUEUE_RESPONSE = 'aln_response.queue';

    public function __construct(
        #[Autowire('%env(string:RABBITMQ_HOST)%')]
        protected readonly string $host,
        #[Autowire('%env(string:RABBITMQ_PORT)%')]
        protected readonly int $port,
        #[Autowire('%env(string:RABBITMQ_USERNAME)%')]
        private readonly string $username,
        #[Autowire('%env(string:RABBITMQ_PASSWORD)%')]
        private readonly string $password,
    ) {
    }

    protected function getQueueConnection(): AMQPStreamConnection
    {
        return new AMQPStreamConnection($this->host, $this->port, $this->username, $this->password);
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
