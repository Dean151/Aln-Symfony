<?php

declare(strict_types=1);

namespace App\Socket;

use App\ApiPlatform\Dto\MealInput;
use App\ApiPlatform\Dto\TimeInput;

use function Safe\hex2bin;
use function Safe\preg_match;

trait MessageTranscriber
{
    protected static function decodeIdentifier(string $hexadecimalIdentifier): string
    {
        $identifier = hex2bin($hexadecimalIdentifier);
        if (!preg_match('/^\w+$/', $identifier)) {
            throw new \RuntimeException('Identifier contains invalid characters');
        }

        return $identifier;
    }

    /**
     * @return int<5, 150>
     */
    protected static function decodeMealAmount(string $hexadecimalMealAmount): int
    {
        $mealAmount = (int) hexdec($hexadecimalMealAmount);
        if ($mealAmount < 5 || $mealAmount > 150) {
            throw new \RuntimeException("Amount $mealAmount is out of bounds [5...150]");
        }

        return $mealAmount;
    }

    /**
     * @return array<MealInput>
     */
    protected static function decodePlanning(string $hexadecimalMeals): array
    {
        $hexadecimalCount = substr($hexadecimalMeals, 0, 2);
        $hexadecimalMeals = substr($hexadecimalMeals, 2);
        $meals = [];
        for ($left = hexdec($hexadecimalCount); $left > 0; --$left) {
            $hexadecimalTime = substr($hexadecimalMeals, 0, 4);
            $hexadecimalAmount = substr($hexadecimalMeals, 4, 4);
            $time = self::decodeTime($hexadecimalTime);
            $amount = self::decodeMealAmount($hexadecimalAmount);
            $meals[] = new MealInput($time, $amount);

            $hexadecimalMeals = substr($hexadecimalMeals, 8);
        }

        return $meals;
    }

    protected static function decodeTime(string $hexadecimalTime): TimeInput
    {
        $globalMinutes = (int) hexdec($hexadecimalTime);
        $hours = ((($globalMinutes - ($globalMinutes % 60)) / 60) + 16) % 24;
        $minutes = ($globalMinutes % 60);
        assert($hours >= 0);
        assert($minutes >= 0);

        return new TimeInput($hours, $minutes);
    }

    /**
     * @param int<5, 150> $mealAmount
     */
    protected function encodeMealAmount(int $mealAmount): string
    {
        return str_pad(dechex($mealAmount), 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param MealInput[] $meals
     */
    protected function encodePlanning(array $meals): string
    {
        $enabled = array_filter($meals, function (MealInput $meal) {
            return $meal->isEnabled;
        });
        $hexadecimalCount = str_pad(dechex(count($enabled)), 2, '0', STR_PAD_LEFT);
        $hexadecimalMeals = implode(array_map(function (MealInput $meal) {
            $hexadecimalTime = $this->encodeTime($meal->time);
            $hexadecimalAmount = $this->encodeMealAmount($meal->amount);

            return $hexadecimalTime.$hexadecimalAmount;
        }, $enabled));

        return $hexadecimalCount.$hexadecimalMeals;
    }

    protected function encodeTime(TimeInput $time): string
    {
        $hoursWithOffset = ($time->hours + 8) % 24;
        $numberOfMinutes = $hoursWithOffset * 60 + $time->minutes;
        $b2 = $numberOfMinutes % 256;
        $b1 = ($numberOfMinutes - $b2) / 256;

        return implode(array_map(fn ($b) => str_pad(dechex((int) $b), 2, '0', STR_PAD_LEFT), [$b1, $b2]));
    }
}
