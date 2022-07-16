<?php

namespace App\Aln\Socket\Messages;

use App\Aln\Socket\MessageTranscriber;

abstract class IdentifiedMessage implements IncomingMessageInterface
{
    use MessageTranscriber;

    protected string $identifier;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
