<?php

namespace App\Aln\Socket\Messages;

final class PlanningChangedMessage extends IdentifiedMessage
{
    public static function decodeFrom(string $hexadecimal): self
    {
        $hexadecimalIdentifier = substr($hexadecimal, 6, -10);
        $identifier = self::decodeIdentifier($hexadecimalIdentifier);

        return new PlanningChangedMessage($identifier);
    }

    public function hexadecimal(): string
    {
        return '9da114'.bin2hex($this->identifier).'c4d0a10000';
    }
}
