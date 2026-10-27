<?php

declare(strict_types=1);

namespace App\Health;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface HealthCheckInterface
{
    public function isHealthy(): bool;
}
