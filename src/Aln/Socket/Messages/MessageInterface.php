<?php

namespace App\Aln\Socket\Messages;

interface MessageInterface
{
    /**
     * @throws \RuntimeException
     */
    public static function decodeFrom(string $hexadecimal): self;

    public function hexadecimal(): string;
}
