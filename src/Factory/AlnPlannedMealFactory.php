<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AlnPlannedMeal;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<AlnPlannedMeal>
 */
final class AlnPlannedMealFactory extends PersistentProxyObjectFactory
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
