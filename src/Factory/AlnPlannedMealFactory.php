<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AlnPlannedMeal;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AlnPlannedMeal>
 */
final class AlnPlannedMealFactory extends PersistentObjectFactory
{
    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'time' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'amount' => self::faker()->numberBetween(5, 150),
            'isEnabled' => self::faker()->boolean(90),
        ];
    }

    public static function class(): string
    {
        return AlnPlannedMeal::class;
    }
}
