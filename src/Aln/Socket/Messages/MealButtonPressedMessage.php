<?php

namespace App\Aln\Socket\Messages;

final class MealButtonPressedMessage extends IdentifiedMessage
{
    private int $mealQuantity;

    public function __construct(string $hexadecimal)
    {
        $hexadecimalIdentifier = substr($hexadecimal, 6, -10);
        $hexadecimalMealAmount = substr($hexadecimal, -4);
        $this->identifier = $this->decodeIdentifier($hexadecimalIdentifier);
        $this->mealQuantity = $this->decodeMealAmount($hexadecimalMealAmount);
    }

    /**
     * @return-stan int<5, 150>
     */
    public function getMealQuantity(): int
    {
        return $this->mealQuantity;
    }
}
