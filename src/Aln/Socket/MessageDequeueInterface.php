<?php

namespace App\Aln\Socket;

use Bunny\Message;
use Ratchet\MessageComponentInterface;

interface MessageDequeueInterface extends MessageComponentInterface
{
    public function dequeueMessage(Message $message): void;
}
