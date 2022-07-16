<?php

namespace App\Aln\Socket\Messages;

final class PlanningChangedMessage extends IdentifiedMessage
{
    public function __construct(string $hexadecimal)
    {
        $hexadecimalIdentifier = substr($hexadecimal, 6, -10);
        $this->identifier = $this->decodeIdentifier($hexadecimalIdentifier);
    }
}