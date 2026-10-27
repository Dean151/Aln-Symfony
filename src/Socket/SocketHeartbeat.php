<?php

declare(strict_types=1);

namespace App\Socket;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Clock\ClockInterface;

/**
 * Shared between the socket daemon and the web app / deploy scripts.
 * Backed by a filesystem pool under the share dir, so it survives cache:clear.
 *
 * The daemon beats periodically, and exits gracefully when a restart has been requested
 * after it started, so that its process manager (e.g. supervisor) starts it again.
 */
final class SocketHeartbeat
{
    public const INTERVAL = 10;
    public const MAX_AGE = 3 * self::INTERVAL;
    public const RESTART_CHECK_INTERVAL = 1;

    private const HEARTBEAT_KEY = 'socket_heartbeat';
    private const RESTART_KEY = 'socket_restart_requested_at';

    public function __construct(
        private readonly CacheItemPoolInterface $heartbeatCache,
        private readonly ClockInterface $clock,
    ) {
    }

    public function beat(float $startedAt): void
    {
        $this->write(self::HEARTBEAT_KEY, ['beat' => $this->now(), 'started' => $startedAt]);
    }

    public function isAlive(): bool
    {
        $heartbeat = $this->readHeartbeat();

        return null !== $heartbeat && $this->now() - $heartbeat['beat'] <= self::MAX_AGE;
    }

    public function requestRestart(): float
    {
        $requestedAt = $this->now();
        $this->write(self::RESTART_KEY, $requestedAt);

        return $requestedAt;
    }

    public function isRestartRequested(float $startedAt): bool
    {
        $requestedAt = $this->read(self::RESTART_KEY);

        return is_float($requestedAt) && $requestedAt > $startedAt;
    }

    public function hasRestartedSince(float $requestedAt): bool
    {
        $heartbeat = $this->readHeartbeat();

        return null !== $heartbeat && $heartbeat['started'] > $requestedAt && $this->isAlive();
    }

    public function now(): float
    {
        return (float) $this->clock->now()->format('U.u');
    }

    /**
     * @return array{beat: float, started: float}|null
     */
    private function readHeartbeat(): ?array
    {
        $heartbeat = $this->read(self::HEARTBEAT_KEY);
        if (!is_array($heartbeat) || !is_float($heartbeat['beat'] ?? null) || !is_float($heartbeat['started'] ?? null)) {
            return null;
        }

        return ['beat' => $heartbeat['beat'], 'started' => $heartbeat['started']];
    }

    private function read(string $key): mixed
    {
        $item = $this->heartbeatCache->getItem($key);

        return $item->isHit() ? $item->get() : null;
    }

    private function write(string $key, mixed $value): void
    {
        $item = $this->heartbeatCache->getItem($key);
        $item->set($value);
        $this->heartbeatCache->save($item);
    }
}
