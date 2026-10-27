<?php

declare(strict_types=1);

namespace App\Health;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(index: 'database')]
final class DatabaseHealthCheck implements HealthCheckInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function isHealthy(): bool
    {
        try {
            $this->connection->executeQuery($this->connection->getDatabasePlatform()->getDummySelectSQL());

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
