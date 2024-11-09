<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AlnPlanning;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<AlnPlanning>
 */
final class AlnPlanningFactory extends PersistentProxyObjectFactory
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
