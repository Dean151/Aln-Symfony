<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
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
        $newName = AlnFeederFactory::faker()->paragraph(16);
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

    /**
     * @env AUTHENTICATION_ENABLED=true
     */
    public function testUpdatingFeederNameOwnedFeeder(): void
    {
        $newName = AlnFeederFactory::faker()->firstName();
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->putFeederNameRequest($id, $newName, $this->getUserByEmail('user.feeder@example.com'));
        $this->assertResponseIsSuccessful();
    }

    /**
     * @env AUTHENTICATION_ENABLED=true
     */
    public function testUpdatingFeederNameUnownedFeeder(): void
    {
        $newName = AlnFeederFactory::faker()->firstName();
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->putFeederNameRequest($id, $newName, $this->getUserByEmail('user.nofeeder@example.com'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @env AUTHENTICATION_ENABLED=true
     */
    public function testUpdatingFeederNameUnauthenticated(): void
    {
        $newName = AlnFeederFactory::faker()->firstName();
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->putFeederNameRequest($id, $newName);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function putFeederNameRequest(int $feederId, string $newName, ?UserInterface $authenticatedAs = null): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('PUT', "/feeders/{$feederId}", $this->getOptions($authenticatedAs)->setJson([
            'name' => $newName,
        ])->toArray());
    }
}
