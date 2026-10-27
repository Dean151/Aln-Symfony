<?php

declare(strict_types=1);

namespace App\Health;

use App\Socket\SocketHeartbeat;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(index: 'socket')]
final class SocketHealthCheck implements HealthCheckInterface
{
    public function __construct(
        private readonly SocketHeartbeat $heartbeat,
    ) {
    }

    public function isHealthy(): bool
    {
        return $this->heartbeat->isAlive();
    }
}
