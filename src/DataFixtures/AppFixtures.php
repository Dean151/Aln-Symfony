<?php

namespace App\DataFixtures;

use App\Factory\AlnFeederFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $availableFeeder = AlnFeederFactory::createOne([
            'identifier' => AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER,
        ]);
        $unavailableFeeder = AlnFeederFactory::createOne([
            'identifier' => AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER,
        ]);
        $notRespondingFeeder = AlnFeederFactory::createOne([
            'identifier' => AlnFeederFactory::NOT_RESPONDING_FEEDER_IDENTIFIER,
        ]);

        $manager->flush();
    }
}
