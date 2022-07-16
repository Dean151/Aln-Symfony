<?php

namespace App\Aln\Socket\Messages;

interface IncomingMessageInterface
{
    /**
     * @throws \RuntimeException
     */
    public function __construct(string $hexadecimal);
}
