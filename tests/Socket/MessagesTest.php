<?php

declare(strict_types=1);

namespace App\Tests\Socket;

use App\ApiPlatform\Dto\MealInput;
use App\ApiPlatform\Dto\TimeInput;
use App\Socket\MessageIdentification;
use App\Socket\Messages\ChangeDefaultMealMessage;
use App\Socket\Messages\ChangePlanningMessage;
use App\Socket\Messages\DefaultMealChangedMessage;
use App\Socket\Messages\ExpectableMessageInterface;
use App\Socket\Messages\ExpectationMessage;
use App\Socket\Messages\FeedNowMessage;
use App\Socket\Messages\IdentificationMessage;
use App\Socket\Messages\MealButtonPressedMessage;
use App\Socket\Messages\MealDistributedMessage;
use App\Socket\Messages\PlanningChangedMessage;
use App\Socket\Messages\TimeMessage;
use PHPUnit\Framework\TestCase;

final class MessagesTest extends TestCase
{
    /**
     * @dataProvider provideIdentificationData
     */
    public function testIdentification(string $hexadecimal, string $expectedIdentifier): void
    {
        $message = MessageIdentification::identifyIncomingMessage($hexadecimal);
        $this->assertInstanceOf(IdentificationMessage::class, $message);
        $this->assertEquals($expectedIdentifier, $message->getIdentifier());
        $this->assertStringStartsWith($message->hexadecimal(), $hexadecimal);
    }

    public function provideIdentificationData(): \Generator
    {
        yield ['9da11441424331323334353637383901d0010000', 'ABC123456789'];
        yield ['9da1145a595839383736353433323101d0010000', 'ZYX987654321'];
        // Messages are sometime doubled. Bad luck...
        yield ['9da11441424331323334353637383901d00100009da11441424331323334353637383901d0010000', 'ABC123456789'];
        // Or even tripled
        yield ['9da11441424331323334353637383901d00100009da11441424331323334353637383901d00100009da1145a595839383736353433323101d0010000', 'ABC123456789'];
    }

    /**
     * @dataProvider provideDefaultMealChangedData
     */
    public function testDefaultMealChanged(string $hexadecimal, string $expectedIdentifier): void
    {
        $message = MessageIdentification::identifyIncomingMessage($hexadecimal);
        $this->assertInstanceOf(DefaultMealChangedMessage::class, $message);
        $this->assertEquals($expectedIdentifier, $message->getIdentifier());
        $this->assertEquals($hexadecimal, $message->hexadecimal());
    }

    public function provideDefaultMealChangedData(): \Generator
    {
        yield ['9da114414243313233343536373839c3d0a10000', 'ABC123456789'];
        yield ['9da1145a5958393837363534333231c3d0a10000', 'ZYX987654321'];
    }

    /**
     * @dataProvider providePlanningChangedData
     */
    public function testPlanningChanged(string $hexadecimal, string $identifier): void
    {
        $message = MessageIdentification::identifyIncomingMessage($hexadecimal);
        $this->assertInstanceOf(PlanningChangedMessage::class, $message);
        $this->assertEquals($identifier, $message->getIdentifier());
        $this->assertEquals($hexadecimal, (new PlanningChangedMessage($identifier))->hexadecimal());
    }

    public function providePlanningChangedData(): \Generator
    {
        yield ['9da114414243313233343536373839c4d0a10000', 'ABC123456789'];
        yield ['9da1145a5958393837363534333231c4d0a10000', 'ZYX987654321'];
    }

    /**
     * @dataProvider provideMealDistributedData
     */
    public function testMealDistributed(string $hexadecimal, string $identifier): void
    {
        $message = MessageIdentification::identifyIncomingMessage($hexadecimal);
        $this->assertInstanceOf(MealDistributedMessage::class, $message);
        $this->assertEquals($identifier, $message->getIdentifier());
        $this->assertEquals($hexadecimal, (new MealDistributedMessage($identifier))->hexadecimal());
    }

    public function provideMealDistributedData(): \Generator
    {
        yield ['9da114414243313233343536373839a2d0a10000', 'ABC123456789'];
        yield ['9da1145a5958393837363534333231a2d0a10000', 'ZYX987654321'];
    }

    /**
     * @param int<5, 150> $mealAmount
     * @dataProvider provideMealButtonPressedData
     */
    public function testMealButtonPressed(string $hexadecimal, string $identifier, TimeInput $time, int $mealAmount): void
    {
        $message = MessageIdentification::identifyIncomingMessage($hexadecimal);
        $this->assertInstanceOf(MealButtonPressedMessage::class, $message);
        $this->assertEquals($identifier, $message->getIdentifier());
        $this->assertEquals($time, $message->getTime());
        $this->assertEquals($mealAmount, $message->getMealAmount());
        $this->assertEquals($hexadecimal, (new MealButtonPressedMessage($identifier, $mealAmount, $time))->hexadecimal());
    }

    public function provideMealButtonPressedData(): \Generator
    {
        yield ['9da11441424331323334353637383921037d001e', 'ABC123456789', new TimeInput(6, 53), 30];
        yield ['9da1145a59583938373635343332312103850005', 'ZYX987654321', new TimeInput(7, 1), 5];
    }

    /**
     * @dataProvider provideTimeData
     */
    public function testTime(string $hexadecimal, TimeInput $time): void
    {
        $this->assertEquals($hexadecimal, (new TimeMessage($time))->hexadecimal());
        $this->assertEquals($time, TimeMessage::decodeFrom($hexadecimal)->getTime());
    }

    public function provideTimeData(): \Generator
    {
        yield ['9da106010000', new TimeInput(16, 0)];
        yield ['9da106010050', new TimeInput(17, 20)];
        yield ['9da10601030c', new TimeInput(5, 0)];
        yield ['9da106010492', new TimeInput(11, 30)];
        yield ['9da10601059f', new TimeInput(15, 59)];
    }

    public function testAllTimes(): void
    {
        for ($hours = 0; $hours < 24; ++$hours) {
            for ($minutes = 0; $minutes < 60; ++$minutes) {
                $time = new TimeInput($hours, $minutes);
                $this->assertMatchesRegularExpression('/^9da106010(?:5\d[[:xdigit:]]|[0-4][[:xdigit:]]{2})$/', (new TimeMessage($time))->hexadecimal());
            }
        }
    }

    /**
     * @param int<5, 150> $mealAmount
     * @dataProvider provideDefaultMealData
     */
    public function testChangeDefaultMeal(string $hexadecimal, int $mealAmount): void
    {
        $this->assertEquals($hexadecimal, (new ChangeDefaultMealMessage($mealAmount))->hexadecimal());
        $this->assertEquals($mealAmount, ChangeDefaultMealMessage::decodeFrom($hexadecimal)->getMealAmount());
    }

    public function provideDefaultMealData(): \Generator
    {
        yield ['9da106c30005', 5];
        yield ['9da106c3000c', 12];
        yield ['9da106c30032', 50];
        yield ['9da106c30064', 100];
        yield ['9da106c30096', 150];
    }

    /**
     * @param int<5, 150> $mealAmount
     * @dataProvider provideFeedNowData
     */
    public function testFeedNow(string $hexadecimal, int $mealAmount): void
    {
        $this->assertEquals($hexadecimal, (new FeedNowMessage($mealAmount))->hexadecimal());
        $this->assertEquals($mealAmount, FeedNowMessage::decodeFrom($hexadecimal)->getMealAmount());
    }

    public function provideFeedNowData(): \Generator
    {
        yield ['9da106a20005', 5];
        yield ['9da106a2000c', 12];
        yield ['9da106a20032', 50];
        yield ['9da106a20064', 100];
        yield ['9da106a20096', 150];
    }

    /**
     * @param array<MealInput> $meals
     * @dataProvider provideChangePlanningData
     */
    public function testChangePlanning(string $hexadecimal, array $meals): void
    {
        $this->assertEquals($hexadecimal, (new ChangePlanningMessage($meals))->hexadecimal());
        $this->assertEquals(array_filter($meals, function ($meal) { return $meal->isEnabled; }), ChangePlanningMessage::decodeFrom($hexadecimal)->getMeals());
    }

    public function provideChangePlanningData(): \Generator
    {
        $meal1 = new MealInput(new TimeInput(11, 30), 10);
        $meal2 = new MealInput(new TimeInput(17, 20), 15);
        $meal3 = new MealInput(new TimeInput(5, 0), 5);
        $meal4 = new MealInput(new TimeInput(5, 0), 5, false);
        yield ['9da12dc400', []];
        yield ['9da12dc4010492000a', [$meal1]];
        yield ['9da12dc4010050000f', [$meal2]];
        yield ['9da12dc401030c0005', [$meal3]];
        yield ['9da12dc4020492000a0050000f', [$meal1, $meal2]];
        yield ['9da12dc4030492000a0050000f030c0005', [$meal1, $meal2, $meal3]];
        yield ['9da12dc4020492000a0050000f', [$meal1, $meal2, $meal4]];
    }

    /**
     * @dataProvider provideResponseMessageDependingOnSentOneData
     */
    public function testResponseMessageDependingOnSentOne(string $identifier, ExpectableMessageInterface $expectable, ExpectationMessage $expectation): void
    {
        $message = $expectable->expectationMessage($identifier);
        $this->assertInstanceOf(get_class($expectation), $message);
        $this->assertEquals($expectation->hexadecimal(), $message->hexadecimal());
    }

    public function provideResponseMessageDependingOnSentOneData(): \Generator
    {
        $identifier = 'ALE291382748';
        yield [$identifier, new ChangeDefaultMealMessage(12), new DefaultMealChangedMessage($identifier)];
        yield [$identifier, new ChangePlanningMessage([]), new PlanningChangedMessage($identifier)];
        yield [$identifier, new FeedNowMessage(24), new MealDistributedMessage($identifier)];
    }
}
