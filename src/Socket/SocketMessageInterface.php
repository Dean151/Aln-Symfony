<?php

declare(strict_types=1);

namespace App\Socket;

use React\Socket\ConnectionInterface;

interface SocketMessageInterface
{
    public function onData(ConnectionInterface $from, string $data): void;

    public function onOpen(ConnectionInterface $conn): void;

    public function onClose(ConnectionInterface $conn): void;

    public function onError(ConnectionInterface $conn, \Exception $e): void;
}
