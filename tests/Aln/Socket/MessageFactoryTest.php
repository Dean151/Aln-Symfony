<?php

namespace App\Tests\Aln\Socket;

use App\Aln\Socket\MessageFactory;
use App\Aln\Socket\Messages\DefaultMealChangedMessage;
use App\Aln\Socket\Messages\EmptyFeederMessage;
use App\Aln\Socket\Messages\IdentificationMessage;
use App\Aln\Socket\Messages\MealButtonPressedMessage;
use App\Aln\Socket\Messages\MealDistributedMessage;
use App\Aln\Socket\Messages\PlanningChangedMessage;
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
        $this->assertEquals($expectedMealAmount, $message->getMealQuantity());
    }

    public function provideMealButtonPressedData(): \Generator
    {
        yield ['9da1144142433132333435363738392103840005', 'ABC123456789', 5];
        yield ['9da1145a59583938373635343332312103840018', 'ZYX987654321', 24];
    }

    /**
     * @param array<string, int> $expectedTime
     * @dataProvider provideEmptyFeederData
     */
    public function testEmptyFeeder(string $hexadecimal, string $expectedIdentifier, array $expectedTime, int $expectedMealAmount): void
    {
        $message = $this->getFactory()->identifyIncoming($hexadecimal);
        $this->assertInstanceOf(EmptyFeederMessage::class, $message);
        $this->assertEquals($expectedIdentifier, $message->getIdentifier());
        $this->assertEquals($expectedTime, $message->getTime());
        $this->assertEquals($expectedMealAmount, $message->getMealQuantity());
    }

    public function provideEmptyFeederData(): \Generator
    {
        yield ['9da11441424331323334353637383921037d001e', 'ABC123456789', ['hours' => 6, 'minutes' => 53], 30];
        yield ['9da1145a59583938373635343332312103850005', 'ZYX987654321', ['hours' => 7, 'minutes' => 1], 5];
    }

    public function testTime(): void
    {
        $this->assertEquals('9da106010000', $this->getFactory()->time(16, 0)->hexadecimal());
        $this->assertEquals('9da10601059f', $this->getFactory()->time(15, 59)->hexadecimal());
    }

    public function testCurrentTime(): void
    {
        // Time goes from 0000 to 05a0 excluded
        $this->assertMatchesRegularExpression('/^9da106010(?:5\d[[:xdigit:]]|[0-4][[:xdigit:]]{2})$/', $this->getFactory()->currentTime()->hexadecimal());
    }

    public function testChangeDefaultMeal(): void
    {
        $this->assertEquals('9da106c30005', $this->getFactory()->changeDefaultMeal(5)->hexadecimal());
        $this->assertEquals('9da106c3000c', $this->getFactory()->changeDefaultMeal(12)->hexadecimal());
        $this->assertEquals('9da106c30032', $this->getFactory()->changeDefaultMeal(50)->hexadecimal());
        $this->assertEquals('9da106c30064', $this->getFactory()->changeDefaultMeal(100)->hexadecimal());
        $this->assertEquals('9da106c30096', $this->getFactory()->changeDefaultMeal(150)->hexadecimal());
    }

    public function testFeedNow(): void
    {
        $this->assertEquals('9da106a20005', $this->getFactory()->feedNow(5)->hexadecimal());
        $this->assertEquals('9da106a2000c', $this->getFactory()->feedNow(12)->hexadecimal());
        $this->assertEquals('9da106a20032', $this->getFactory()->feedNow(50)->hexadecimal());
        $this->assertEquals('9da106a20064', $this->getFactory()->feedNow(100)->hexadecimal());
        $this->assertEquals('9da106a20096', $this->getFactory()->feedNow(150)->hexadecimal());
    }

    public function testChangePlanning(): void
    {
        $meal1 = ['time' => ['hours' => 11, 'minutes' => 30], 'amount' => 10];
        $meal2 = ['time' => ['hours' => 17, 'minutes' => 20], 'amount' => 15];
        $meal3 = ['time' => ['hours' => 5, 'minutes' => 0], 'amount' => 5];
        $this->assertEquals('9da12dc400', $this->getFactory()->changePlanning([])->hexadecimal());
        $this->assertEquals('9da12dc4010492000a', $this->getFactory()->changePlanning([$meal1])->hexadecimal());
        $this->assertEquals('9da12dc4010050000f', $this->getFactory()->changePlanning([$meal2])->hexadecimal());
        $this->assertEquals('9da12dc401030c0005', $this->getFactory()->changePlanning([$meal3])->hexadecimal());
        $this->assertEquals('9da12dc4020492000a0050000f', $this->getFactory()->changePlanning([$meal1, $meal2])->hexadecimal());
        $this->assertEquals('9da12dc4030492000a0050000f030c0005', $this->getFactory()->changePlanning([$meal1, $meal2, $meal3])->hexadecimal());
    }
}
