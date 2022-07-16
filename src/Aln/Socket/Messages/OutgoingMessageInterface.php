<?php

namespace App\Aln\Socket\Messages;

interface OutgoingMessageInterface
{
    public function hexadecimal(): string;
}
