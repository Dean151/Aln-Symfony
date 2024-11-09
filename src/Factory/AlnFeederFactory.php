<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AlnFeeder;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<AlnFeeder>
 */
final class AlnFeederFactory extends PersistentProxyObjectFactory
{
    public const AVAILABLE_FEEDER_IDENTIFIER = 'ABC123456789';
    public const UNAVAILABLE_FEEDER_IDENTIFIER = 'ZYX987654321';
    public const NOT_RESPONDING_FEEDER_IDENTIFIER = 'OBU293827463';
    public const EMPTY_FEEDER_IDENTIFIER = 'ABE382749283';

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'identifier' => self::faker()->bothify('???#########'),
            'name' => self::faker()->firstName(),
            'lastSeen' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'owner' => null,
            'ip' => self::faker()->ipv4(),
        ];
    }

    public static function class(): string
    {
        return AlnFeeder::class;
    }
}
