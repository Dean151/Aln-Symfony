<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ChangeDefaultMealApiTest extends FeederApiTestCase
{
    public function testChangeDefaultMeal(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->changeDefaultMealRequest($id, $amount);
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals([
            'message' => "{$amount}g meal is now the default amount",
        ]);

        $feeder = $this->findFeeder(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->assertEquals($amount, $feeder->getDefaultMealAmount());

        $this->getFeederRequest($id);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'defaultMealAmount' => $amount,
        ]);
    }

    #[DataProvider('provideNonValidInputData')]
    public function testChangeDefaultMealWithNonValidInput(int $amount): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->changeDefaultMealRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function provideNonValidInputData(): \Generator
    {
        yield [4];
        yield [151];
        yield [random_int(151, PHP_INT_MAX)];
    }

    public function testChangeDefaultMealWithUnavailableFeeder(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $this->changeDefaultMealRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testChangeDefaultMealWithNonRespondingFeeder(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::NOT_RESPONDING_FEEDER_IDENTIFIER);
        $this->changeDefaultMealRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function testChangeDefaultMealWithUnknownFeederId(): void
    {
        $amount = random_int(5, 150);
        $id = random_int(0, PHP_INT_MAX);
        $this->changeDefaultMealRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @env AUTHENTICATION_ENABLED=true
     */
    public function testChangeDefaultMealOwnedFeeder(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->changeDefaultMealRequest($id, $amount, $this->getUserByEmail('user.feeder@example.com'));
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals([
            'message' => "{$amount}g meal is now the default amount",
        ]);
    }

    /**
     * @env AUTHENTICATION_ENABLED=true
     */
    public function testChangeDefaultMealUnownedFeeder(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->changeDefaultMealRequest($id, $amount, $this->getUserByEmail('user.nofeeder@example.com'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @env AUTHENTICATION_ENABLED=true
     */
    public function testChangeDefaultMealUnauthenticated(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->changeDefaultMealRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function changeDefaultMealRequest(int $feederId, int $amount, ?UserInterface $authenticatedAs = null): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('PUT', "/feeders/{$feederId}/amount", $this->getOptions($authenticatedAs)->setJson([
            'amount' => $amount,
        ])->toArray());
    }

    private function getFeederRequest(int $feederId): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('GET', "/feeders/{$feederId}", $this->getOptions()->toArray());
    }
}
