<?php

declare(strict_types=1);

namespace App\Socket;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Clock\ClockInterface;

/**
 * Shared between the socket daemon (writer) and the web app (reader).
 * Backed by a filesystem pool under the share dir, so it survives cache:clear.
 */
final class SocketHeartbeat
{
    public const INTERVAL = 10;
    public const MAX_AGE = 3 * self::INTERVAL;

    private const KEY = 'socket_heartbeat';

    public function __construct(
        private readonly CacheItemPoolInterface $heartbeatCache,
        private readonly ClockInterface $clock,
    ) {
    }

    public function beat(): void
    {
        $item = $this->heartbeatCache->getItem(self::KEY);
        $item->set($this->clock->now()->getTimestamp());
        $this->heartbeatCache->save($item);
    }

    public function isAlive(): bool
    {
        $item = $this->heartbeatCache->getItem(self::KEY);
        $lastBeat = $item->isHit() ? $item->get() : null;
        if (!is_int($lastBeat)) {
            return false;
        }

        return $this->clock->now()->getTimestamp() - $lastBeat <= self::MAX_AGE;
    }
}
