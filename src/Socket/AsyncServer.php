<?php

declare(strict_types=1);

namespace App\Socket;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

final class AsyncServer
{
    private ContainerBagInterface $params;
    private LoggerInterface $logger;

    private ?SocketServer $socket = null;

    public function __construct(ContainerBagInterface $params, LoggerInterface $logger)
    {
        $this->params = $params;
        $this->logger = $logger;
    }

    public function start(LoopInterface $loop, SocketMessageInterface $socketInterface): void
    {
        $host = $this->params->get('websocket.host');
        $port = $this->params->get('websocket.port');

        $bind = 'tcp://'.$host.':'.$port;

        $this->socket = new SocketServer($bind, [], $loop);
        $this->socket->on('connection', function (ConnectionInterface $connection) use ($socketInterface) {
            $socketInterface->onOpen($connection);
            $connection->on('data', function ($data) use ($connection, $socketInterface) {
                $socketInterface->onData($connection, $data);
            });
            $connection->on('close', function () use ($connection, $socketInterface) {
                $socketInterface->onClose($connection);
            });
            $connection->on('error', function (\Exception $e) use ($connection, $socketInterface) {
                $socketInterface->onError($connection, $e);
            });
        });
        $this->logger->info("Started websocket server on {$bind}");
    }

    public function shutdown(): void
    {
        if ($this->socket) {
            $this->socket->close();
            $this->socket = null;
            $this->logger->info('Stopped websocket consumer');
        }
    }
}
