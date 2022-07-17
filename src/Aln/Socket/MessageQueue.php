<?php

namespace App\Aln\Socket;

use App\Aln\Socket\Messages\MessageInterface;
use App\Entity\AlnFeeder;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Safe\Exceptions\StringsException;

final class MessageQueue implements MessageEnqueueInterface
{
    public const QUEUE_SOCKET = 'aln_socket';

    /**
     * @throws StringsException|\Exception
     */
    public function enqueueSocketMessageAndWait(AlnFeeder $feeder, MessageInterface $message, float $timeout = 5): bool
    {
        $identifier = $feeder->getIdentifier();
        if (!is_string($identifier)) {
            throw new \InvalidArgumentException('Feeder have no identifier!');
        }

        $msg = new AMQPMessage(implode('|', [$identifier, $message->hexadecimal()]));

        return $this->publishInQueueAndWait(self::QUEUE_SOCKET, $msg, $timeout);
    }

    private function getQueueConnection(): AMQPStreamConnection
    {
        $host = $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1';
        $port = $_ENV['RABBITMQ_PORT'] ?? 5672;
        $user = $_ENV['RABBITMQ_USERNAME'] ?? 'guest';
        $password = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';

        return new AMQPStreamConnection($host, $port, $user, $password);
    }

    /**
     * @param-stan self::QUEUE_* $queue
     */
    private function publishInQueueAndWait(string $queue, AMQPMessage $message, float $timeout = 5): bool
    {
        $connection = $this->getQueueConnection();
        $channel = $connection->channel();

        $channel->queue_declare($queue, false, false, false, false);
        $channel->confirm_select();
        $channel->basic_publish($message, '', $queue);

        $fulfilled = false;
        // FIXME: Seems like all messages are fulfilled...
        $channel->set_ack_handler(function (AMQPMessage $acked) use (&$fulfilled, $message) {
            if ($acked->getBody() === $message->getBody()) {
                $fulfilled = true;
            }
        });
        // FIXME: Seems like all ack is sent immediatly
        $channel->wait_for_pending_acks($timeout);

        $channel->close();
        $connection->close();

        return $fulfilled;
    }
}
