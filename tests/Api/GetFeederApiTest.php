<?php

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;

final class GetFeederApiTest extends FeederApiTest
{
    public function testAvailableFeederStatus(): void
    {
        $feeder = $this->findFeeder(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('GET', "/api/feeders/{$feeder->getId()}", [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'id' => $feeder->getId(),
            'identifier' => AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER,
            'name' => $feeder->getName(),
            'lastSeen' => $feeder->getLastSeen()->format('c'),
            'defaultMealAmount' => $feeder->getDefaultMealAmount(),
            'isAvailable' => true,
        ]);
    }

    public function testUnavailableFeederStatus(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('GET', "/api/feeders/{$id}", [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'id' => $id,
            'identifier' => AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER,
            'defaultMealAmount' => null,
            'isAvailable' => false,
        ]);
    }

    public function testUnknownFeederId(): void
    {
        $id = random_int(0, PHP_INT_MAX);
        $client = self::createClient();
        $client->request('GET', "/api/feeders/{$id}", [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
