<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AlnMeal;
use App\Repository\AlnMealRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<AlnMeal>
 *
 * @method static        AlnMeal|Proxy createOne(array $attributes = [])
 * @method static        AlnMeal[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static        AlnMeal|Proxy find(object|array|mixed $criteria)
 * @method static        AlnMeal|Proxy findOrCreate(array $attributes)
 * @method static        AlnMeal|Proxy first(string $sortedField = 'id')
 * @method static        AlnMeal|Proxy last(string $sortedField = 'id')
 * @method static        AlnMeal|Proxy random(array $attributes = [])
 * @method static        AlnMeal|Proxy randomOrCreate(array $attributes = [])
 * @method static        AlnMeal[]|Proxy[] all()
 * @method static        AlnMeal[]|Proxy[] findBy(array $attributes)
 * @method static        AlnMeal[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static        AlnMeal[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static        AlnMealRepository|RepositoryProxy repository()
 * @method AlnMeal|Proxy create(array|callable $attributes = [])
 */
final class AlnMealFactory extends ModelFactory
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
        return AlnMeal::class;
    }
}
