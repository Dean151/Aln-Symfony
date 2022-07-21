<?php

namespace App\Aln\Queue;

use App\Aln\Socket\Messages\ExpectableMessageInterface;
use App\Entity\AlnFeeder;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;

final class SocketMessageQueue extends AbstractQueue implements MessageEnqueueInterface
{
    /**
     * @throws \Exception
     */
    public function enqueueSocketMessageAndWait(AlnFeeder $feeder, ExpectableMessageInterface $message, float $timeout = 5): bool
    {
        $identifier = $feeder->getIdentifier();
        if (!is_string($identifier)) {
            throw new \InvalidArgumentException('Feeder have no identifier!');
        }

        $ampqMessage = new AMQPMessage(implode('|', [$identifier, $message->hexadecimal()]));

        return $this->publishInQueueAndWait($ampqMessage, $message->expectationMessage($identifier)->hexadecimal(), $timeout);
    }

    /**
     * @param-stan self::QUEUE_* $queue
     */
    private function publishInQueueAndWait(AMQPMessage $message, string $expectation, float $timeout = 5): bool
    {
        $connection = $this->getQueueConnection();
        $channel = $connection->channel();

        $channel->queue_declare(AbstractQueue::QUEUE_RESPONSE, false, false, false, false);

        $fulfilled = false;

        $consumerTag = null;
        $callback = function (AMQPMessage $message) use ($channel, &$consumerTag, $expectation, &$fulfilled) {
            if ($message->getBody() !== $expectation) {
                return;
            }
            $fulfilled = true;
            $channel->basic_cancel($consumerTag); // @phpstan-ignore-line
        };

        $consumerTag = $channel->basic_consume(self::QUEUE_RESPONSE, '', false, true, false, false, $callback);
        $this->publishInQueue($channel, self::QUEUE_SOCKET, $message);
        try {
            $channel->wait(null, false, $timeout);
        } catch (AMQPTimeoutException $e) {
        }

        $channel->close();
        $connection->close();

        return $fulfilled;
    }
}
