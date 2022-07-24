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
        $this->associatedFeederRequest(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER, $user);
        $this->assertResponseIsSuccessful();

        $feeder = $this->findFeeder(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $this->assertNotNull($feeder->getOwner());
        $this->assertEquals($user->getId(), $feeder->getOwner()->getId());
    }

    public function testFeederAssociationUnauthenticated(): void
    {
        $this->associatedFeederRequest(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @depends testFeederAssociation
     */
    public function testFeederAlreadyAssociated(): void
    {
        $user = $this->getUserByEmail('user.nofeeder@example.com');
        $this->associatedFeederRequest(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER, $user);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testFeederAssociationUnknown(): void
    {
        $this->associatedFeederRequest('unkwown');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function associatedFeederRequest(string $feederIdentifier, ?UserInterface $authenticatedAs = null): ResponseInterface
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
}
