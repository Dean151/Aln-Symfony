<?php

namespace App\Socket\Messages;

use App\Socket\MessageTranscriber;

abstract class IdentifiedMessage implements MessageInterface
{
    use MessageTranscriber;

    protected string $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
