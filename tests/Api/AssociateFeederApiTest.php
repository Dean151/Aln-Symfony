<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @env AUTHENTICATION_ENABLED=true
 */
final class AssociateFeederApiTest extends FeederApiTestCase
{
    public function testFeederAssociation(): void
    {
        $user = $this->getUserByEmail('user.feeder@example.com');
        $this->associateFeederRequest(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER, $user);
        $this->assertResponseIsSuccessful();

        $feeder = $this->findFeeder(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $this->assertNotNull($feeder->getOwner());
        $this->assertEquals($user->getId(), $feeder->getOwner()->getId());
    }

    public function testFeederAssociationWrongIp(): void
    {
        $user = $this->getUserByEmail('user.feeder@example.com');
        $this->associateFeederRequest(AlnFeederFactory::EMPTY_FEEDER_IDENTIFIER, $user);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testFeederAssociationUnauthenticated(): void
    {
        $this->associateFeederRequest(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @depends testFeederAssociation
     */
    public function testFeederAlreadyAssociated(): void
    {
        $user = $this->getUserByEmail('user.nofeeder@example.com');
        $this->associateFeederRequest(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER, $user);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testFeederAssociationUnknown(): void
    {
        $user = $this->getUserByEmail('user.nofeeder@example.com');
        $this->associateFeederRequest('unkwown', $user);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @depends testFeederAlreadyAssociated
     */
    public function testDissociateFeeder(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $user = $this->getUserByEmail('user.feeder@example.com');
        $this->dissociateFeederRequest($id, $user);
        $this->assertResponseIsSuccessful();

        $feeder = $this->findFeeder(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $this->assertNull($feeder->getOwner());
    }

    /**
     * @depends testDissociateFeeder
     */
    public function testFeederAlreadyDissociated(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $user = $this->getUserByEmail('user.feeder@example.com');
        $this->dissociateFeederRequest($id, $user);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDissociateUnownedFeeder(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $user = $this->getUserByEmail('user.nofeeder@example.com');
        $this->dissociateFeederRequest($id, $user);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDissociateUnauthenticated(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->dissociateFeederRequest($id);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testDissociateUnknownFeeder(): void
    {
        $id = random_int(0, PHP_INT_MAX);
        $user = $this->getUserByEmail('user.nofeeder@example.com');
        $this->dissociateFeederRequest($id, $user);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function associateFeederRequest(string $feederIdentifier, ?UserInterface $authenticatedAs = null): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('POST', '/feeders/associate', [
            'headers' => [
                    'Accept' => 'application/json',
                ] + $this->getHeadersIfAuthenticated($authenticatedAs),
            'json' => [
                'identifier' => $feederIdentifier,
            ],
        ]);
    }

    private function dissociateFeederRequest(int $feederId, ?UserInterface $authenticatedAs = null): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('DELETE', "/feeders/{$feederId}/association", [
            'headers' => [
                    'Accept' => 'application/json',
                ] + $this->getHeadersIfAuthenticated($authenticatedAs),
        ]);
    }
}
