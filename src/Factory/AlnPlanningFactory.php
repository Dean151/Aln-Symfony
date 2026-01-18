<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AlnPlanning;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AlnPlanning>
 */
final class AlnPlanningFactory extends PersistentObjectFactory
{
    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdOn' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    public static function class(): string
    {
        return AlnPlanning::class;
    }
}
