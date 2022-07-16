<?php

namespace App\Aln\Socket;

use App\Aln\Socket\Messages\OutgoingMessageInterface;
use App\Entity\AlnFeeder;

interface MessageEnqueueInterface
{
    public function enqueueMessage(AlnFeeder $feeder, OutgoingMessageInterface $message): void;
}
