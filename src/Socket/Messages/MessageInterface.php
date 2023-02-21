<?php

declare(strict_types=1);

namespace App\Socket\Messages;

use App\Socket\MessageTranscriber;

abstract class MessageInterface
{
    use MessageTranscriber;

    /**
     * @throws \RuntimeException
     */
    abstract public static function decodeFrom(string $hexadecimal): self;

    /**
     * @return non-empty-string
     */
    abstract public function hexadecimal(): string;
}
