<?php

declare(strict_types=1);

namespace App\Socket\Messages;

abstract class ExpectableMessageInterface extends MessageInterface
{
    abstract public function expectationMessage(string $identifier): ExpectationMessage;
}
