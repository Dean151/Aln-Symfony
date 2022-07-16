<?php

namespace App\Aln\Socket;

use App\Aln\Socket\Messages\MessageInterface;
use App\Entity\AlnFeeder;

interface MessageEnqueueInterface
{
    public function enqueueMessage(AlnFeeder $feeder, MessageInterface $message): void;
}
