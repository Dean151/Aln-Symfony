<?php

namespace App\Aln\Socket\Messages;

use App\Aln\Socket\MessageTranscriber;

class ChangePlanningMessage implements MessageInterface
{
    use MessageTranscriber;

    /**
     * @var array<array{time: array{hours: int<0, 23>, minutes: int<0, 59>}, amount: int<5, 150>}>
     */
    private array $meals;

    public static function decodeFrom(string $hexadecimal): self
    {
        $hexadecimalPlanning = substr($hexadecimal, 8);
        $meals = self::decodePlanning($hexadecimalPlanning);

        return new ChangePlanningMessage($meals);
    }

    /**
     * @param array<array{time: array{hours: int<0, 23>, minutes: int<0, 59>}, amount: int<5, 150>}> $meals
     */
    public function __construct(array $meals)
    {
        $this->meals = $meals;
    }

    /**
     * @return array<array{time: array{hours: int<0, 23>, minutes: int<0, 59>}, amount: int<5, 150>}>
     */
    public function getMeals(): array
    {
        return $this->meals;
    }

    public function getCount(): int
    {
        return count($this->meals);
    }

    public function hexadecimal(): string
    {
        $prefix = '9da12dc4';
        $planning = $this->encodePlanning($this->meals);

        return $prefix.$planning;
    }
}
