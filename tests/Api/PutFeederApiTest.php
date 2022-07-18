<?php

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Zenstruck\Foundry\Test\Factories;

final class PutFeederApiTest extends FeederApiTestCase
{
    use Factories;

    public function testUpdatingFeederName(): void
    {
        $newName = AlnFeederFactory::faker()->name();
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->putFeederNameRequest($id, $newName);

        $feeder = $this->findFeeder(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->assertEquals($newName, $feeder->getName());
    }

    public function testUpdatingWithOutOfBoundName(): void
    {
        $newName = AlnFeederFactory::faker()->paragraph(10);
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->putFeederNameRequest($id, $newName);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdatingUnknownFeederId(): void
    {
        $newName = AlnFeederFactory::faker()->firstName();
        $id = random_int(0, PHP_INT_MAX);
        $this->putFeederNameRequest($id, $newName);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function putFeederNameRequest(int $feederId, string $newName): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('PUT', "/api/feeders/{$feederId}", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'name' => $newName,
            ],
        ]);
    }
}
