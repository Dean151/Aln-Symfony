<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Zalas\PHPUnit\Globals\Attribute\Env;
use Zenstruck\Foundry\Test\Factories;

use function Zenstruck\Foundry\faker;

final class PutFeederApiTest extends FeederApiTestCase
{
    use Factories;

    public function testUpdatingFeederName(): void
    {
        $newName = faker()->name();
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->putFeederNameRequest($id, $newName);

        $feeder = $this->findFeeder(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->assertEquals($newName, $feeder->getName());
    }

    public function testUpdatingWithOutOfBoundName(): void
    {
        $newName = faker()->paragraph(16);
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->putFeederNameRequest($id, $newName);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUpdatingUnknownFeederId(): void
    {
        $newName = faker()->firstName();
        $id = random_int(0, PHP_INT_MAX);
        $this->putFeederNameRequest($id, $newName);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Env('AUTHENTICATION_ENABLED', 'true')]
    public function testUpdatingFeederNameOwnedFeeder(): void
    {
        $newName = faker()->firstName();
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->putFeederNameRequest($id, $newName, $this->getUserByEmail('user.feeder@example.com'));
        $this->assertResponseIsSuccessful();
    }

    #[Env('AUTHENTICATION_ENABLED', 'true')]
    public function testUpdatingFeederNameUnownedFeeder(): void
    {
        $newName = faker()->firstName();
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->putFeederNameRequest($id, $newName, $this->getUserByEmail('user.nofeeder@example.com'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Env('AUTHENTICATION_ENABLED', 'true')]
    public function testUpdatingFeederNameUnauthenticated(): void
    {
        $newName = faker()->firstName();
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
