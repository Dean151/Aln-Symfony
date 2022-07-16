<?php

namespace App\Aln\Socket;

use function Safe\hex2bin;
use function Safe\preg_match;

trait MessageTranscriber
{
    protected function decodeIdentifier(string $hexadecimalIdentifier): string
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
    protected function decodeMealAmount(string $hexadecimalMealAmount): int
    {
        $mealAmount = (int) hexdec($hexadecimalMealAmount);
        if ($mealAmount < 5 || $mealAmount > 150) {
            throw new \RuntimeException('Amount is out of bounds [5...150]');
        }

        return $mealAmount;
    }

    /**
     * @param int<5, 150> $mealAmount
     */
    protected function encodeMealAmount(int $mealAmount): string
    {
        return str_pad(dechex($mealAmount), 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<string, int>
     */
    protected function decodeTime(string $hexadecimalTime): array
    {
        $globalMinutes = (int) hexdec($hexadecimalTime);
        $hours = ((($globalMinutes - ($globalMinutes % 60)) / 60) + 16) % 24;
        $minutes = ($globalMinutes % 60);

        return ['hours' => $hours, 'minutes' => $minutes];
    }

    /**
     * @param int<0, 23> $hours
     * @param int<0, 59> $minutes
     */
    protected function encodeTime(int $hours, int $minutes): string
    {
        assert($hours % 24 == $hours);
        assert($minutes % 60 == $minutes);
        $hoursWithOffset = ($hours + 8) % 24;
        $numberOfMinutes = $hoursWithOffset * 60 + $minutes;
        $b2 = $numberOfMinutes % 256;
        $b1 = ($numberOfMinutes - $b2) / 256;

        return implode(array_map(fn ($b) => str_pad(dechex((int) $b), 2, '0', STR_PAD_LEFT), [$b1, $b2]));
    }
}
