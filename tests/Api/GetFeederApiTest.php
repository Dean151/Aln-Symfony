<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Zalas\PHPUnit\Globals\Attribute\Env;

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

    #[Env('AUTHENTICATION_ENABLED', 'true')]
    public function testFeederStatusOwnedFeeder(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->getFeederRequest($id, $this->getUserByEmail('user.feeder@example.com'));
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'id' => $id,
        ]);
    }

    #[Env('AUTHENTICATION_ENABLED', 'true')]
    public function testFeederStatusUnownedFeeder(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->getFeederRequest($id, $this->getUserByEmail('user.nofeeder@example.com'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Env('AUTHENTICATION_ENABLED', 'true')]
    public function testFeederStatusUnauthenticated(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->getFeederRequest($id);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function getFeederRequest(int $feederId, ?UserInterface $authenticatedAs = null): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('GET', "/feeders/{$feederId}", $this->getOptions($authenticatedAs)->toArray());
    }
}
