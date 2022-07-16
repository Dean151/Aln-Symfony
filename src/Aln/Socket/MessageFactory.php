<?php

namespace App\Aln\Socket;

use App\Aln\Socket\Messages\ChangeDefaultMealMessage;
use App\Aln\Socket\Messages\DefaultMealChangedMessage;
use App\Aln\Socket\Messages\EmptyFeederMessage;
use App\Aln\Socket\Messages\FeedNowMessage;
use App\Aln\Socket\Messages\IdentificationMessage;
use App\Aln\Socket\Messages\IncomingMessageInterface;
use App\Aln\Socket\Messages\MealButtonPressedMessage;
use App\Aln\Socket\Messages\MealDistributedMessage;
use App\Aln\Socket\Messages\PlanningChangedMessage;
use App\Aln\Socket\Messages\TimeMessage;
use Safe\DateTimeImmutable;
use Safe\Exceptions\PcreException;

use function Safe\preg_match;

final class MessageFactory
{
    /**
     * @throws PcreException|\RuntimeException
     */
    public function identifyIncoming(string $hexadecimal): IncomingMessageInterface
    {
        if (!str_starts_with($hexadecimal, '9da114')) {
            throw new \RuntimeException('All incoming messages should start with 9da114');
        }
        if (str_ends_with($hexadecimal, '01d0010000')) {
            return new IdentificationMessage($hexadecimal);
        }
        if (str_ends_with($hexadecimal, 'c3d0a10000')) {
            return new DefaultMealChangedMessage($hexadecimal);
        }
        if (str_ends_with($hexadecimal, 'c4d0a10000')) {
            return new PlanningChangedMessage($hexadecimal);
        }
        if (str_ends_with($hexadecimal, 'a2d0a10000')) {
            return new MealDistributedMessage($hexadecimal);
        }
        if (preg_match('/21038400([0-9a-f]{2})$/', $hexadecimal, $matches)) {
            return new MealButtonPressedMessage($hexadecimal);
        }
        if (preg_match('/210(?:5\d[[:xdigit:]]|[0-4][[:xdigit:]]{2})(00[0-9][0-9a-f])$/', $hexadecimal, $matches)) {
            return new EmptyFeederMessage($hexadecimal);
        }
        throw new \RuntimeException('Unknown incoming message: '.$hexadecimal);
    }

    /**
     * @param int<0, 23> $hours
     * @param int<0, 59> $minutes
     */
    public function time(int $hours, int $minutes): TimeMessage
    {
        return new TimeMessage($hours, $minutes);
    }

    public function currentTime(): TimeMessage
    {
        $currentDatetime = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $hours = (int) $currentDatetime->format('H');
        $minutes = (int) $currentDatetime->format('i');
        assert($minutes >= 0 && $minutes < 60);

        return $this->time($hours, $minutes);
    }

    /**
     * @param int<5, 150> $mealAmount
     */
    public function changeDefaultMeal(int $mealAmount): ChangeDefaultMealMessage
    {
        return new ChangeDefaultMealMessage($mealAmount);
    }

    /**
     * @param int<5, 150> $mealAmount
     */
    public function feedNow(int $mealAmount): FeedNowMessage
    {
        return new FeedNowMessage($mealAmount);
    }

    // TODO: add planning change!
}
