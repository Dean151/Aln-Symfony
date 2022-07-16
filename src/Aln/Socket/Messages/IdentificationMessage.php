<?php

namespace App\Aln\Socket\Messages;

use function Safe\preg_match;

final class IdentificationMessage extends IdentifiedMessage
{
    public static function decodeFrom(string $hexadecimal): self
    {
        foreach ([3, 2, 1] as $repeat) {
            if (preg_match("/^(?:9da114([0-9a-f]+)01d0010000){{$repeat}}$/", $hexadecimal, $matches)) {
                break;
            }
        }
        if (empty($matches)) {
            throw new \RuntimeException('Unrecognized hexadecimal for IdentificationMessage');
        }

        $hexadecimalIdentifier = $matches[1];
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
