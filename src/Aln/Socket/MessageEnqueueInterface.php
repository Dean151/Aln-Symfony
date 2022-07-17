<?php

namespace App\Aln\Socket;

use App\Aln\Socket\Messages\MessageInterface;
use App\Entity\AlnFeeder;
use PhpAmqpLib\Exception\AMQPTimeoutException;

interface MessageEnqueueInterface
{
    /**
     * @throws AMQPTimeoutException
     */
    public function enqueueSocketMessageAndWait(AlnFeeder $feeder, MessageInterface $message, float $timeout = 5): bool;
}
