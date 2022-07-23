<?php

declare(strict_types=1);

namespace App\Socket\Messages;

use App\Socket\MessageTranscriber;

abstract class IdentifiedMessage extends MessageInterface
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
