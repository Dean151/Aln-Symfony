<?php

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;

final class PutFeederApiTest extends FeederApiTest
{
    use Factories;

    public function testUpdatingFeederName(): void
    {
        $newName = AlnFeederFactory::faker()->name();
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('PUT', "/api/feeders/{$id}", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'name' => $newName,
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => $newName,
        ]);

        $feeder = $this->findFeeder(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->assertEquals($newName, $feeder->getName());
    }

    public function testUpdatingWithOutOfBoundName(): void
    {
        $newName = AlnFeederFactory::faker()->paragraph(10);
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $client = self::createClient();
        $client->request('PUT', "/api/feeders/{$id}", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'name' => $newName,
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdatingUnknownFeederId(): void
    {
        $newName = AlnFeederFactory::faker()->firstName();
        $id = random_int(0, PHP_INT_MAX);
        $client = self::createClient();
        $client->request('PUT', "/api/feeders/{$id}", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'name' => $newName,
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
