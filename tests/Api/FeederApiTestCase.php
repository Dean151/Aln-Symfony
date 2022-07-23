<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\AlnFeeder;
use App\Repository\AlnFeederRepository;

abstract class FeederApiTestCase extends ApiTestCase
{
    protected function findFeeder(string $identifier): AlnFeeder
    {
        $repository = $this->getContainer()->get(AlnFeederRepository::class);
        $this->assertInstanceOf(AlnFeederRepository::class, $repository);
        $feeder = $repository->findOneByIdentifier($identifier);
        $this->assertInstanceOf(AlnFeeder::class, $feeder);

        return $feeder;
    }

    protected function findFeederId(string $identifier): int
    {
        $id = $this->findFeeder($identifier)->getId();
        $this->assertIsInt($id);

        return $id;
    }
}
