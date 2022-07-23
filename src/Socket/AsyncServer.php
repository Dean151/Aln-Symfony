<?php

declare(strict_types=1);

namespace App\Socket;

use Psr\Log\LoggerInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\LoopInterface;
use React\Socket\SocketServer;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

final class AsyncServer
{
    private ContainerBagInterface $params;
    private LoggerInterface $logger;

    private ?IoServer $server = null;

    public function __construct(ContainerBagInterface $params, LoggerInterface $logger)
    {
        $this->params = $params;
        $this->logger = $logger;
    }

    public function start(LoopInterface $loop, MessageComponentInterface $component): void
    {
        $host = $this->params->get('websocket.host');
        $port = $this->params->get('websocket.port');

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
