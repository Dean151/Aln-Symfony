<?php

namespace App\Socket\Messages;

interface ExpectableMessageInterface extends MessageInterface
{
    public function expectationMessage(string $identifier): ExpectationMessage;
}
