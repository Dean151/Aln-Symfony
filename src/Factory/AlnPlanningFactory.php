<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AlnPlanning;
use App\Repository\AlnPlanningRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<AlnPlanning>
 *
 * @method static AlnPlanning|Proxy                     createOne(array $attributes = [])
 * @method static AlnPlanning[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static AlnPlanning|Proxy                     find(object|array|mixed $criteria)
 * @method static AlnPlanning|Proxy                     findOrCreate(array $attributes)
 * @method static AlnPlanning|Proxy                     first(string $sortedField = 'id')
 * @method static AlnPlanning|Proxy                     last(string $sortedField = 'id')
 * @method static AlnPlanning|Proxy                     random(array $attributes = [])
 * @method static AlnPlanning|Proxy                     randomOrCreate(array $attributes = [])
 * @method static AlnPlanning[]|Proxy[]                 all()
 * @method static AlnPlanning[]|Proxy[]                 findBy(array $attributes)
 * @method static AlnPlanning[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 * @method static AlnPlanning[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static AlnPlanningRepository|RepositoryProxy repository()
 * @method        AlnPlanning|Proxy                     create(array|callable $attributes = [])
 */
final class AlnPlanningFactory extends ModelFactory
{
    /**
     * @return array<string, mixed>
     */
    protected function getDefaults(): array
    {
        return [
            'createdOn' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    protected static function getClass(): string
    {
        return AlnPlanning::class;
    }
}
