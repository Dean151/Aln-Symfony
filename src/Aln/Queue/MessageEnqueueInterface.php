<?php

namespace App\Aln\Queue;

use App\Aln\Socket\Messages\ExpectableMessageInterface;
use App\Entity\AlnFeeder;

interface MessageEnqueueInterface
{
    public function enqueueSocketMessageAndWait(AlnFeeder $feeder, ExpectableMessageInterface $message, float $timeout = 5): bool;
}
