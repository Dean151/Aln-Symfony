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
        // FIXME: clean this with parameters
        $host = $_ENV['WEBSOCKET_HOST'] ?? '0.0.0.0';
        $port = $_ENV['WEBSOCKET_PORT'] ?? 9999;

        $wsServer = new WsServer($component);
        $httpServer = new HttpServer($wsServer);
        $socketServer = new SocketServer($host.':'.$port, [], $loop);
        $this->server = new IoServer($httpServer, $socketServer, $loop);
        $this->logger->info("Started websocket server on {$host}:{$port}");
    }

    public function shutdown(): void
    {
        if ($this->server) {
            $this->server->socket->close();
            $this->server = null;
            $this->logger->info('Stopped websocket consumer');
        }
    }
}
