<?php

namespace App\Aln\Socket\Messages;

interface IncomingMessageInterface extends OutgoingMessageInterface
{
    /**
     * @throws \RuntimeException
     */
    public static function decodeFrom(string $hexadecimal): self;
}
