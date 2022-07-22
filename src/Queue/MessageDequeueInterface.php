<?php

namespace App\Queue;

use PhpAmqpLib\Message\AMQPMessage;
use Ratchet\MessageComponentInterface;

interface MessageDequeueInterface extends MessageComponentInterface
{
    public function dequeueMessage(AMQPMessage $amqpMessage): void;
}
