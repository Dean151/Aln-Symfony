<?php

namespace App\Socket;

use Psr\Log\LoggerInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\LoopInterface;
use React\Socket\SocketServer;

final class AsyncServer
{
    private LoggerInterface $logger;

    private ?IoServer $server = null;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function start(LoopInterface $loop, MessageComponentInterface $component): void
    {
        // FIXME: clean this
        $wsHost = $_ENV['WEBSOCKET_HOST'] ?? '0.0.0.0';
        $wsPort = $_ENV['WEBSOCKET_PORT'] ?? 9999;

        $wsServer = new WsServer($component);
        $httpServer = new HttpServer($wsServer);
        $socketServer = new SocketServer($wsHost.':'.$wsPort, [], $loop);
        $this->server = new IoServer($httpServer, $socketServer, $loop);
        $this->logger->info("Started Ratchet websocket server on {$wsHost}:{$wsPort}");
    }

    public function shutdown(): void
    {
        $this->server?->socket->close();
    }
}
