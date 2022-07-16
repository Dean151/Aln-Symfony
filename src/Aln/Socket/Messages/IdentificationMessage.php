<?php

namespace App\Aln\Socket\Messages;

use function Safe\preg_match;

final class IdentificationMessage extends IdentifiedMessage
{
    public function __construct(string $hexadecimal)
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
        $this->identifier = $this->decodeIdentifier($hexadecimalIdentifier);
    }
}
