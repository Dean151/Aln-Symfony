<?php

declare(strict_types=1);

namespace App\Health;

use App\Queue\AbstractQueue;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(index: 'queue')]
final class QueueHealthCheck extends AbstractQueue implements HealthCheckInterface
{
    public function isHealthy(): bool
    {
        try {
            $this->getQueueConnection()->close();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
