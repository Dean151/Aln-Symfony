<?php

namespace App\Socket\Messages;

final class IdentificationMessage extends IdentifiedMessage
{
    public static function decodeFrom(string $hexadecimal): self
    {
        $hexadecimalIdentifier = substr($hexadecimal, 6, 24);
        $identifier = self::decodeIdentifier($hexadecimalIdentifier);

        return new IdentificationMessage($identifier);
    }

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function hexadecimal(): string
    {
        return '9da114'.bin2hex($this->identifier).'01d0010000';
    }
}
