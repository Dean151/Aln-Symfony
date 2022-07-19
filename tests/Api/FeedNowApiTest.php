<?php

namespace App\Tests\Api;

use App\Entity\AlnMeal;
use App\Factory\AlnFeederFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class FeedNowApiTest extends FeederApiTestCase
{
    public function testFeedNow(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->feedNowRequest($id, $amount);
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals([
            'message' => "{$amount}g meal has been distributed",
        ]);

        $feeder = $this->findFeeder(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $meal = $feeder->getMeals()->last();
        $this->assertInstanceOf(AlnMeal::class, $meal);
        $this->assertNotNull($meal->getDistributedOn());
        $this->assertEquals($amount, $meal->getAmount());

        // Planning reserved settings should be null
        $this->assertNull($meal->getTime());
        $this->assertNull($meal->getPlanning());
    }

    /**
     * @dataProvider provideNonValidInputData
     */
    public function testFeedNowWithNonValidInput(int $amount): void
    {
        $id = $this->findFeederId(AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->feedNowRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function provideNonValidInputData(): \Generator
    {
        yield [4];
        yield [151];
        yield [random_int(151, PHP_INT_MAX)];
    }

    public function testFeedNowWithUnavailableFeeder(): void
    {
        $amount = random_int(5, 150);
        $id = $this->findFeederId(AlnFeederFactory::UNAVAILABLE_FEEDER_IDENTIFIER);
        $this->feedNowRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testFeedNowWithUnknownFeederId(): void
    {
        $amount = random_int(5, 150);
        $id = random_int(0, PHP_INT_MAX);
        $this->feedNowRequest($id, $amount);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function feedNowRequest(int $feederId, int $amount): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('POST', "/api/feeders/{$feederId}/feed", [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'amount' => $amount,
            ],
        ]);
    }
}
