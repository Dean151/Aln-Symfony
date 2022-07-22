<?php

namespace App\Queue;

use PhpAmqpLib\Message\AMQPMessage;

interface MessageDequeueInterface
{
    public function dequeueMessage(AMQPMessage $amqpMessage): void;
}
