<?php

declare(strict_types=1);

namespace App\Socket\Messages;

use App\ApiPlatform\Dto\TimeInput;

final class MealButtonPressedMessage extends IdentifiedMessage
{
    private TimeInput $previousMeal;

    /**
     * @var int<5, 150>
     */
    private int $mealAmount;

    public static function decodeFrom(string $hexadecimal): self
    {
        $hexadecimalIdentifier = substr($hexadecimal, 6, -10);
        $hexadecimalTime = substr($hexadecimal, -8, 4);
        $hexadecimalMealAmount = substr($hexadecimal, -4);
        $identifier = self::decodeIdentifier($hexadecimalIdentifier);
        $previousMeal = self::decodeTime($hexadecimalTime);
        $mealAmount = self::decodeMealAmount($hexadecimalMealAmount);

        return new MealButtonPressedMessage($identifier, $mealAmount, $previousMeal);
    }

    /**
     * @param int<5, 150> $mealAmount
     */
    public function __construct(string $identifier, int $mealAmount, TimeInput $previousMeal)
    {
        $this->previousMeal = $previousMeal;
        $this->mealAmount = $mealAmount;
        parent::__construct($identifier);
    }

    public function getPreviousMeal(): TimeInput
    {
        return $this->previousMeal;
    }

    /**
     * @return int<5, 150>
     */
    public function getMealAmount(): int
    {
        return $this->mealAmount;
    }

    public function hexadecimal(): string
    {
        $previousMeal = $this->encodeTime($this->previousMeal);
        $amount = $this->encodeMealAmount($this->mealAmount);

        return '9da114'.bin2hex($this->identifier).'21'.$previousMeal.$amount;
    }
}
