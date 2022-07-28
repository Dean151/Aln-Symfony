<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AlnFeeder;
use App\Repository\AlnFeederRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<AlnFeeder>
 *
 * @method static          AlnFeeder|Proxy createOne(array $attributes = [])
 * @method static          AlnFeeder[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static          AlnFeeder|Proxy find(object|array|mixed $criteria)
 * @method static          AlnFeeder|Proxy findOrCreate(array $attributes)
 * @method static          AlnFeeder|Proxy first(string $sortedField = 'id')
 * @method static          AlnFeeder|Proxy last(string $sortedField = 'id')
 * @method static          AlnFeeder|Proxy random(array $attributes = [])
 * @method static          AlnFeeder|Proxy randomOrCreate(array $attributes = [])
 * @method static          AlnFeeder[]|Proxy[] all()
 * @method static          AlnFeeder[]|Proxy[] findBy(array $attributes)
 * @method static          AlnFeeder[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static          AlnFeeder[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static          AlnFeederRepository|RepositoryProxy repository()
 * @method AlnFeeder|Proxy create(array|callable $attributes = [])
 */
final class AlnFeederFactory extends ModelFactory
{
    public const AVAILABLE_FEEDER_IDENTIFIER = 'ABC123456789';
    public const UNAVAILABLE_FEEDER_IDENTIFIER = 'ZYX987654321';
    public const NOT_RESPONDING_FEEDER_IDENTIFIER = 'OBU293827463';
    public const EMPTY_FEEDER_IDENTIFIER = 'ABE382749283';

    /**
     * @return array<string, mixed>
     */
    protected function getDefaults(): array
    {
        return [
            'identifier' => self::faker()->bothify('???#########'),
            'name' => self::faker()->firstName(),
            'lastSeen' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'owner' => null,
        ];
    }

    protected static function getClass(): string
    {
        return AlnFeeder::class;
    }
}
