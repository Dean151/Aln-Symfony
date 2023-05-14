<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\AlnManualMeal;
use App\Factory\AlnFeederFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class TriggerMealApiTest extends FeederApiTestCase
{
    public function testTriggerMeal(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->triggerMealRequest($id, $amount);
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals([
            'message' => "{$amount}g meal has been distributed",
        ]);

        $feeder = $this->findFeeder(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $meal = $feeder->getManualMeals()->last();
        $this->assertInstanceOf(AlnManualMeal::class, $meal);
        $this->assertNotNull($meal->getDistributedOn());
        $this->assertEquals($amount, $meal->getAmount());
    }

    #[DataProvider('provideNonValidInputData')]
    public function testTriggerMealWithNonValidInput(int $amount): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->triggerMealRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function provideNonValidInputData(): \Generator
    {
        yield [4];
        yield [151];
        yield [random_int(151, PHP_INT_MAX)];
    }

    public function testTriggerMealWithUnavailableFeeder(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $this->triggerMealRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testTriggerMealWithNonRespondingFeeder(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::NOT_RESPONDING_FEEDER_IDENTIFIER);
        $this->triggerMealRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function testTriggerMealWithUnknownFeederId(): void
    {
        $amount = random_int(5, 150);
        $id = random_int(0, PHP_INT_MAX);
        $this->triggerMealRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @env AUTHENTICATION_ENABLED=true
     */
    public function testTriggerMealOwnedFeeder(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->triggerMealRequest($id, $amount, $this->getUserByEmail('user.feeder@example.com'));
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals([
            'message' => "{$amount}g meal has been distributed",
        ]);
    }

    /**
     * @env AUTHENTICATION_ENABLED=true
     */
    public function testTriggerMealUnownedFeeder(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->triggerMealRequest($id, $amount, $this->getUserByEmail('user.nofeeder@example.com'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @env AUTHENTICATION_ENABLED=true
     */
    public function testTriggerMealUnauthenticated(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->triggerMealRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function triggerMealRequest(int $feederId, int $amount, ?UserInterface $authenticatedAs = null): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('POST', "/feeders/{$feederId}/feed", $this->getOptions($authenticatedAs)->setJson([
            'amount' => $amount,
        ])->toArray());
    }
}
