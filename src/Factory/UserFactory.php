<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'identifier' => self::faker()->unique()->uuid(),
            'email' => self::faker()->unique()->email(),
            'roles' => [],
            'password' => '$2y$13$NhZZh8rS5HoDvO4U4V4mpOE2B6kfzNr5G4rN0CXb.txbX7pI6zIhS', // Pre-hashed text "password"
        ];
    }

    public static function class(): string
    {
        return User::class;
    }
}
