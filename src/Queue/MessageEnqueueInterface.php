<?php

declare(strict_types=1);

namespace App\Queue;

use App\Entity\AlnFeeder;
use App\Socket\Messages\ExpectableMessageInterface;

interface MessageEnqueueInterface
{
    public function enqueueSocketMessageAndWait(AlnFeeder $feeder, ExpectableMessageInterface $message, float $timeout = 5): bool;
}
