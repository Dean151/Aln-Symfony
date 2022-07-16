<?php

namespace App\Aln\Socket\Messages;

final class MealButtonPressedMessage extends IdentifiedMessage
{
    /**
     * @var int<5, 150>
     */
    private int $mealAmount;

    public static function decodeFrom(string $hexadecimal): self
    {
        $hexadecimalIdentifier = substr($hexadecimal, 6, -10);
        $hexadecimalMealAmount = substr($hexadecimal, -4);
        $identifier = self::decodeIdentifier($hexadecimalIdentifier);
        $mealAmount = self::decodeMealAmount($hexadecimalMealAmount);

        return new MealButtonPressedMessage($identifier, $mealAmount);
    }

    /**
     * @param int<5, 150> $mealAmount
     */
    public function __construct(string $identifier, int $mealAmount)
    {
        $this->mealAmount = $mealAmount;
        parent::__construct($identifier);
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
        $amount = $this->encodeMealAmount($this->mealAmount);

        return '9da114'.bin2hex($this->identifier).'210384'.$amount;
    }
}
