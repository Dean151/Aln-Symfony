<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\AlnFeederFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $userWithFeeders = UserFactory::createOne([
            'email' => 'user.feeder@example.com',
        ]);
        $userWithoutFeeder = UserFactory::createOne([
            'email' => 'user.nofeeder@example.com',
        ]);

        $availableFeeder = AlnFeederFactory::createOne([
            'identifier' => AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER,
            'owner' => $userWithFeeders,
            'ip' => '127.0.0.1',
        ]);
        $unavailableFeeder = AlnFeederFactory::createOne([
            'identifier' => AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER,
            'ip' => '127.0.0.1',
        ]);
        $notRespondingFeeder = AlnFeederFactory::createOne([
            'identifier' => AlnFeederFactory::NOT_RESPONDING_FEEDER_IDENTIFIER,
        ]);
        $emptyFeder = AlnFeederFactory::createOne([
            'identifier' => AlnFeederFactory::EMPTY_FEEDER_IDENTIFIER,
        ]);

        $manager->flush();
    }
}
