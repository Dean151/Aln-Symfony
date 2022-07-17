<?php

namespace App\Aln\Socket;

use PhpAmqpLib\Message\AMQPMessage;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

interface MessageDequeueInterface extends MessageComponentInterface
{
    public function dequeueMessageAndWait(AMQPMessage $message, LoopInterface $loop, float $timeout = 5): PromiseInterface;
}
