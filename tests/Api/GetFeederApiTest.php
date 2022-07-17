<?php

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;

final class GetFeederApiTest extends FeederApiTest
{
    public function testAvailableFeederStatus(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('GET', "/api/feeders/{$id}", [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'id' => $id,
            'identifier' => AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER,
            'defaultMealAmount' => null,
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
