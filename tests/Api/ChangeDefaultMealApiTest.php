<?php

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;

class ChangeDefaultMealApiTest extends FeederApiTest
{
    public function testChangeDefaultMeal(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('PUT', "/api/feeders/{$id}/amount", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'amount' => 12,
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals([
            'message' => '12g meal is now the default amount',
        ]);

        $feeder = $this->findFeeder(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->assertEquals(12, $feeder->getDefaultMealAmount());
    }

    public function testChangeDefaultMealWithTooSmallAmount(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('PUT', "/api/feeders/{$id}/amount", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'amount' => 4,
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testChangeDefaultMealWithTooBigAmount(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('PUT', "/api/feeders/{$id}/amount", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'amount' => 151,
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testChangeDefaultMealWithUnavailableFeeder(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('PUT', "/api/feeders/{$id}/amount", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'amount' => 75,
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testChangeDefaultMealWithUnknownFeederId(): void
    {
        $id = random_int(0, PHP_INT_MAX);
        $client = self::createClient();
        $client->request('PUT', "/api/feeders/{$id}/amount", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'amount' => 42,
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
