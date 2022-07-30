<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AlnPlannedMeal;
use App\Repository\AlnPlannedMealRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<AlnPlannedMeal>
 *
 * @method static               AlnPlannedMeal|Proxy createOne(array $attributes = [])
 * @method static               AlnPlannedMeal[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static               AlnPlannedMeal|Proxy find(object|array|mixed $criteria)
 * @method static               AlnPlannedMeal|Proxy findOrCreate(array $attributes)
 * @method static               AlnPlannedMeal|Proxy first(string $sortedField = 'id')
 * @method static               AlnPlannedMeal|Proxy last(string $sortedField = 'id')
 * @method static               AlnPlannedMeal|Proxy random(array $attributes = [])
 * @method static               AlnPlannedMeal|Proxy randomOrCreate(array $attributes = [])
 * @method static               AlnPlannedMeal[]|Proxy[] all()
 * @method static               AlnPlannedMeal[]|Proxy[] findBy(array $attributes)
 * @method static               AlnPlannedMeal[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static               AlnPlannedMeal[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static               AlnPlannedMealRepository|RepositoryProxy repository()
 * @method AlnPlannedMeal|Proxy create(array|callable $attributes = [])
 */
final class AlnPlannedMealFactory extends ModelFactory
{
    /**
     * @return array<string, mixed>
     */
    protected function getDefaults(): array
    {
        return [
            'time' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'amount' => self::faker()->numberBetween(5, 150),
            'isEnabled' => self::faker()->boolean(90),
        ];
    }

    protected static function getClass(): string
    {
        return AlnPlannedMeal::class;
    }
}
