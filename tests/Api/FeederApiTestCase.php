<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\AlnFeeder;
use App\Repository\AlnFeederRepository;

abstract class FeederApiTestCase extends AuthenticatedApiTestCase
{
    protected function findFeeder(string $identifier): AlnFeeder
    {
        $repository = $this->getContainer()->get(AlnFeederRepository::class);
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
