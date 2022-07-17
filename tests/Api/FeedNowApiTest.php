<?php

namespace App\Tests\Api;

use App\Entity\AlnMeal;
use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;

class FeedNowApiTest extends FeederApiTest
{
    public function testFeedNow(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('POST', "/api/feeders/{$id}/feed", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'amount' => 12,
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals([
            'message' => '12g meal has been distributed',
        ]);

        $feeder = $this->findFeeder(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $meal = $feeder->getMeals()->last();
        $this->assertInstanceOf(AlnMeal::class, $meal);
        $this->assertEquals(12, $meal->getAmount());
        $this->assertNotNull($meal->getDate());
        $this->assertNotNull($meal->getTime());
        $this->assertNull($meal->getPlanning());
    }

    public function testFeedNowWithTooSmallAmount(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('POST', "/api/feeders/{$id}/feed", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'amount' => 4,
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testFeedNowWithTooBigAmount(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('POST', "/api/feeders/{$id}/feed", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'amount' => 151,
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testFeedNowWithUnavailableFeeder(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('POST', "/api/feeders/{$id}/feed", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'amount' => 75,
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testFeedNowWithUnknownFeederId(): void
    {
        $id = random_int(0, PHP_INT_MAX);
        $client = self::createClient();
        $client->request('POST', "/api/feeders/{$id}/feed", [
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
