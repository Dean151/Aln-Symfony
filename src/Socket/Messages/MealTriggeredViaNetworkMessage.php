<?php

declare(strict_types=1);

namespace App\Socket\Messages;

final class MealTriggeredViaNetworkMessage extends ExpectationMessage
{
    public static function decodeFrom(string $hexadecimal): self
    {
        $hexadecimalIdentifier = substr($hexadecimal, 6, -10);
        $identifier = self::decodeIdentifier($hexadecimalIdentifier);

        return new MealTriggeredViaNetworkMessage($identifier);
    }

    public function hexadecimal(): string
    {
        return '9da114'.bin2hex($this->identifier).'a2d0a10000';
    }
}
