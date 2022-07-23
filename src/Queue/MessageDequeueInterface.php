<?php

declare(strict_types=1);

namespace App\Queue;

use PhpAmqpLib\Message\AMQPMessage;

interface MessageDequeueInterface
{
    public function dequeueMessage(AMQPMessage $amqpMessage): void;
}
