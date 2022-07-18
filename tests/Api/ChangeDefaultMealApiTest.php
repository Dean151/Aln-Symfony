<?php

namespace App\Tests\Api;

use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;
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
    }

    /**
     * @dataProvider provideNonValidInputData
     */
    public function testChangeDefaultMealWithNonValidInput(int $amount): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->changeDefaultMealRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function provideNonValidInputData(): \Generator
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

    public function testChangeDefaultMealWithUnknownFeederId(): void
    {
        $amount = random_int(5, 150);
        $id = random_int(0, PHP_INT_MAX);
        $this->changeDefaultMealRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function changeDefaultMealRequest(int $feederId, int $amount): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('PUT', "/api/feeders/{$feederId}/amount", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'amount' => $amount,
            ],
        ]);
    }
}
