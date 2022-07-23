<?php

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class GetFeederApiTest extends FeederApiTestCase
{
    public function testAvailableFeederStatus(): void
    {
        $feeder = $this->findFeeder(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->getFeederRequest($feeder->getId() ?? -1);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'id' => $feeder->getId(),
            'identifier' => AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER,
            'name' => $feeder->getName(),
            'lastSeen' => $feeder->getLastSeen()->format('c'),
            'defaultMealAmount' => $feeder->getDefaultMealAmount(),
            'available' => true,
        ]);
    }

    public function testUnavailableFeederStatus(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $this->getFeederRequest($id);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'id' => $id,
            'identifier' => AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER,
            'defaultMealAmount' => null,
            'available' => false,
        ]);
    }

    public function testUnknownFeederId(): void
    {
        $id = random_int(0, PHP_INT_MAX);
        $this->getFeederRequest($id);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function getFeederRequest(int $feederId): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('GET', "/feeders/{$feederId}", [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }
}
