<?php

namespace App\Aln\Socket;

use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

final class FeederCommunicator implements MessageComponentInterface
{
    private FeederCoordinator $coordinator;
    private LoggerInterface $logger;

    /**
     * @var \SplObjectStorage<ConnectionInterface>
     */
    private \SplObjectStorage $connections;

    public function __construct(FeederCoordinator $coordinator, LoggerInterface $logger)
    {
        $this->coordinator = $coordinator;
        $this->logger = $logger;
        $this->connections = new \SplObjectStorage();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->connections->attach($conn);
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->connections->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $hexadecimalMessage = bin2hex($msg);
        $this->logger->debug("Data received: {$hexadecimalMessage}");
        try {
            $this->coordinator->handleSocketMessage($from, $hexadecimalMessage);
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
            $from->close();
        }
    }
}
