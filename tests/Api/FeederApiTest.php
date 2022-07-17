<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\AlnFeeder;
use App\Factory\AlnFeederFactory;
use App\Repository\AlnFeederRepository;
use Symfony\Component\HttpFoundation\Response;

class FeederApiTest extends ApiTestCase
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
        $client = self::createClient();
        $client->request('GET', '/api/feeders/151', [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function findFeederId(string $identifier): int
    {
        $repository = $this->getContainer()->get(AlnFeederRepository::class);
        $this->assertInstanceOf(AlnFeederRepository::class, $repository);
        $feeder = $repository->findOneByIdentifier($identifier);
        $this->assertInstanceOf(AlnFeeder::class, $feeder);
        $id = $feeder->getId();
        $this->assertIsInt($id);

        return $id;
    }
}
