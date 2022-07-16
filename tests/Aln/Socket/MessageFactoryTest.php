<?php

namespace App\Tests\Aln\Socket;

use App\Aln\Socket\MessageFactory;
use App\Aln\Socket\Messages\ChangeDefaultMealMessage;
use App\Aln\Socket\Messages\ChangePlanningMessage;
use App\Aln\Socket\Messages\DefaultMealChangedMessage;
use App\Aln\Socket\Messages\EmptyFeederMessage;
use App\Aln\Socket\Messages\FeedNowMessage;
use App\Aln\Socket\Messages\IdentificationMessage;
use App\Aln\Socket\Messages\MealButtonPressedMessage;
use App\Aln\Socket\Messages\MealDistributedMessage;
use App\Aln\Socket\Messages\PlanningChangedMessage;
use App\Aln\Socket\Messages\TimeMessage;
use PHPUnit\Framework\TestCase;

final class MessageFactoryTest extends TestCase
{
    private ?MessageFactory $factory = null;

    private function getFactory(): MessageFactory
    {
        if (!$this->factory instanceof MessageFactory) {
            $this->factory = new MessageFactory();
        }

        return $this->factory;
    }

    /**
     * @dataProvider provideIdentificationData
     */
    public function testIdentification(string $hexadecimal, string $expectedIdentifier): void
    {
        $message = $this->getFactory()->identifyIncoming($hexadecimal);
        $this->assertInstanceOf(IdentificationMessage::class, $message);
        $this->assertEquals($expectedIdentifier, $message->getIdentifier());
        $this->assertStringStartsWith($message->hexadecimal(), $hexadecimal);
    }

    public function provideIdentificationData(): \Generator
    {
        yield ['9da11441424331323334353637383901d0010000', 'ABC123456789'];
        yield ['9da1145a595839383736353433323101d0010000', 'ZYX987654321'];
        // Messages are sometime doubled, or tripled. Bad luck...
        yield ['9da11441424331323334353637383901d00100009da11441424331323334353637383901d0010000', 'ABC123456789'];
    }

    /**
     * @dataProvider provideDefaultMealChangedData
     */
    public function testDefaultMealChanged(string $hexadecimal, string $expectedIdentifier): void
    {
        $message = $this->getFactory()->identifyIncoming($hexadecimal);
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
    public function testPlanningChanged(string $hexadecimal, string $expectedIdentifier): void
    {
        $message = $this->getFactory()->identifyIncoming($hexadecimal);
        $this->assertInstanceOf(PlanningChangedMessage::class, $message);
        $this->assertEquals($expectedIdentifier, $message->getIdentifier());
        $this->assertEquals($hexadecimal, $message->hexadecimal());
    }

    public function providePlanningChangedData(): \Generator
    {
        yield ['9da114414243313233343536373839c4d0a10000', 'ABC123456789'];
        yield ['9da1145a5958393837363534333231c4d0a10000', 'ZYX987654321'];
    }

    /**
     * @dataProvider provideMealDistributedData
     */
    public function testMealDistributed(string $hexadecimal, string $expectedIdentifier): void
    {
        $message = $this->getFactory()->identifyIncoming($hexadecimal);
        $this->assertInstanceOf(MealDistributedMessage::class, $message);
        $this->assertEquals($expectedIdentifier, $message->getIdentifier());
        $this->assertEquals($hexadecimal, $message->hexadecimal());
    }

    public function provideMealDistributedData(): \Generator
    {
        yield ['9da114414243313233343536373839a2d0a10000', 'ABC123456789'];
        yield ['9da1145a5958393837363534333231a2d0a10000', 'ZYX987654321'];
    }

    /**
     * @dataProvider provideMealButtonPressedData
     */
    public function testMealButtonPressed(string $hexadecimal, string $expectedIdentifier, int $expectedMealAmount): void
    {
        $message = $this->getFactory()->identifyIncoming($hexadecimal);
        $this->assertInstanceOf(MealButtonPressedMessage::class, $message);
        $this->assertEquals($expectedIdentifier, $message->getIdentifier());
        $this->assertEquals($expectedMealAmount, $message->getMealAmount());
        $this->assertEquals($hexadecimal, $message->hexadecimal());
    }

    public function provideMealButtonPressedData(): \Generator
    {
        yield ['9da1144142433132333435363738392103840005', 'ABC123456789', 5];
        yield ['9da1145a59583938373635343332312103840018', 'ZYX987654321', 24];
    }

    /**
     * @param array{hours: int<0, 23>, minutes: int<0, 59>} $expectedTime
     * @dataProvider provideEmptyFeederData
     */
    public function testEmptyFeeder(string $hexadecimal, string $expectedIdentifier, array $expectedTime, int $expectedMealAmount): void
    {
        $message = $this->getFactory()->identifyIncoming($hexadecimal);
        $this->assertInstanceOf(EmptyFeederMessage::class, $message);
        $this->assertEquals($expectedIdentifier, $message->getIdentifier());
        $this->assertEquals($expectedTime, $message->getTime());
        $this->assertEquals($expectedMealAmount, $message->getMealAmount());
        $this->assertEquals($hexadecimal, $message->hexadecimal());
    }

    public function provideEmptyFeederData(): \Generator
    {
        yield ['9da11441424331323334353637383921037d001e', 'ABC123456789', ['hours' => 6, 'minutes' => 53], 30];
        yield ['9da1145a59583938373635343332312103850005', 'ZYX987654321', ['hours' => 7, 'minutes' => 1], 5];
    }

    /**
     * @param array{hours: int<0, 23>, minutes: int<0, 59>} $time
     * @dataProvider provideTimeData
     */
    public function testTime(string $hexadecimal, array $time): void
    {
        $this->assertEquals($hexadecimal, $this->getFactory()->time($time)->hexadecimal());
        $this->assertEquals($time, TimeMessage::decodeFrom($hexadecimal)->getTime());
    }

    public function provideTimeData(): \Generator
    {
        yield ['9da106010000', ['hours' => 16, 'minutes' => 0]];
        yield ['9da106010050', ['hours' => 17, 'minutes' => 20]];
        yield ['9da10601030c', ['hours' => 5, 'minutes' => 0]];
        yield ['9da106010492', ['hours' => 11, 'minutes' => 30]];
        yield ['9da10601059f', ['hours' => 15, 'minutes' => 59]];
    }

    public function testAllTimes(): void
    {
        for ($hours = 0; $hours < 24; ++$hours) {
            for ($minutes = 0; $minutes < 60; ++$minutes) {
                $this->assertMatchesRegularExpression('/^9da106010(?:5\d[[:xdigit:]]|[0-4][[:xdigit:]]{2})$/', $this->getFactory()->time(['hours' => $hours, 'minutes' => $minutes])->hexadecimal());
            }
        }
    }

    /**
     * @param int<5, 150> $mealAmount
     * @dataProvider provideDefaultMealData
     */
    public function testChangeDefaultMeal(string $hexadecimal, int $mealAmount): void
    {
        $this->assertEquals($hexadecimal, $this->getFactory()->changeDefaultMeal($mealAmount)->hexadecimal());
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
        $this->assertEquals($hexadecimal, $this->getFactory()->feedNow($mealAmount)->hexadecimal());
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
     * @param array<array{time: array{hours: int<0, 23>, minutes: int<0, 59>}, amount: int<5, 150>}> $meals
     * @dataProvider provideChangePlanningData
     */
    public function testChangePlanning(string $hexadecimal, array $meals): void
    {
        $this->assertEquals($hexadecimal, $this->getFactory()->changePlanning($meals)->hexadecimal());
        $this->assertEquals($meals, ChangePlanningMessage::decodeFrom($hexadecimal)->getMeals());
    }

    public function provideChangePlanningData(): \Generator
    {
        $meal1 = ['time' => ['hours' => 11, 'minutes' => 30], 'amount' => 10];
        $meal2 = ['time' => ['hours' => 17, 'minutes' => 20], 'amount' => 15];
        $meal3 = ['time' => ['hours' => 5, 'minutes' => 0], 'amount' => 5];
        yield ['9da12dc400', []];
        yield ['9da12dc4010492000a', [$meal1]];
        yield ['9da12dc4010050000f', [$meal2]];
        yield ['9da12dc401030c0005', [$meal3]];
        yield ['9da12dc4020492000a0050000f', [$meal1, $meal2]];
        yield ['9da12dc4030492000a0050000f030c0005', [$meal1, $meal2, $meal3]];
    }
}
