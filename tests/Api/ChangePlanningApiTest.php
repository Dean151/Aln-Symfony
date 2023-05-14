<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ChangePlanningApiTest extends FeederApiTestCase
{
    /**
     * @param array<array<string, mixed>> $meals
     */
    #[DataProvider('providePlanningChangeData')]
    public function testPlanningChange(array $meals): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->changePlanningRequest($id, $meals);
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals([
            'message' => 'Planning have been changed',
        ]);

        $this->getFeederRequest($id);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'currentPlanning' => ['meals' => $meals],
        ]);
    }

    public static function providePlanningChangeData(): \Generator
    {
        $meal1 = ['time' => ['hours' => 11, 'minutes' => 30], 'amount' => 10];
        $meal2 = ['time' => ['hours' => 17, 'minutes' => 20], 'amount' => 15];
        $meal3 = ['time' => ['hours' => 5, 'minutes' => 0], 'amount' => 5, 'enabled' => false];
        yield [[]];
        yield [[$meal1]];
        yield [[$meal1, $meal2]];
        yield [[$meal1, $meal2, $meal3]];
    }

    /**
     * @param-stan Response::HTTP_* $response_code
     */
    #[DataProvider('provideNonValidInputData')]
    public function testPlanningChangeWithNonValidInput(int $response_code, mixed $meals): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->changePlanningRequest($id, $meals);
        $this->assertResponseStatusCodeSame($response_code);
    }

    public static function provideNonValidInputData(): \Generator
    {
        $valid = ['time' => ['hours' => 17, 'minutes' => 20], 'amount' => 15];
        $unvalidHours = ['time' => ['hours' => 24, 'minutes' => 0], 'amount' => 12];
        $unvalidMinutes = ['time' => ['hours' => 12, 'minutes' => 60], 'amount' => 6];
        $unvalidAmount = ['time' => ['hours' => 12, 'minutes' => 0], 'amount' => 2];
        yield [Response::HTTP_UNPROCESSABLE_ENTITY, [$valid, $unvalidHours]];
        yield [Response::HTTP_UNPROCESSABLE_ENTITY, [$valid, $unvalidMinutes]];
        yield [Response::HTTP_UNPROCESSABLE_ENTITY, [$valid, $unvalidAmount]];
    }

    public function testPlanningChangeWithUnavailableFeeder(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $this->changePlanningRequest($id, []);
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testChangeDefaultMealWithNonRespondingFeeder(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::NOT_RESPONDING_FEEDER_IDENTIFIER);
        $this->changePlanningRequest($id, []);
        $this->assertResponseStatusCodeSame(Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function testPlanningChangeWithUnknownFeederId(): void
    {
        $id = random_int(0, PHP_INT_MAX);
        $this->changePlanningRequest($id, []);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @env AUTHENTICATION_ENABLED=true
     */
    public function testPlanningChangeOwnedFeeder(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->changePlanningRequest($id, [], $this->getUserByEmail('user.feeder@example.com'));
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals([
            'message' => 'Planning have been changed',
        ]);
    }

    /**
     * @env AUTHENTICATION_ENABLED=true
     */
    public function testPlanningChangeUnownedFeeder(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->changePlanningRequest($id, [], $this->getUserByEmail('user.nofeeder@example.com'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @env AUTHENTICATION_ENABLED=true
     */
    public function testPlanningChangeUnauthenticated(): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->changePlanningRequest($id, []);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function changePlanningRequest(int $feederId, mixed $meals, ?UserInterface $authenticatedAs = null): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('PUT', "/feeders/{$feederId}/planning", $this->getOptions($authenticatedAs)->setJson([
            'meals' => $meals,
        ])->toArray());
    }

    private function getFeederRequest(int $feederId): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('GET', "/feeders/{$feederId}", $this->getOptions()->toArray());
    }
}
