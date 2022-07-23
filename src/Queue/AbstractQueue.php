<?php

declare(strict_types=1);

namespace App\Queue;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

abstract class AbstractQueue
{
    public const QUEUE_SOCKET = 'aln_socket.queue';
    public const QUEUE_RESPONSE = 'aln_response.queue';

    private ContainerBagInterface $params;

    public function __construct(ContainerBagInterface $params)
    {
        $this->params = $params;
    }

    protected function getHost(): string
    {
        return $this->params->get('rabbitmq.host');
    }

    protected function getPort(): int
    {
        return $this->params->get('rabbitmq.port');
    }

    protected function getQueueConnection(): AMQPStreamConnection
    {
        $username = $this->params->get('rabbitmq.username');
        $password = $this->params->get('rabbitmq.password');

        return new AMQPStreamConnection($this->getHost(), $this->getPort(), $username, $password);
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
