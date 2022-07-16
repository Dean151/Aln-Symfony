<?php

namespace App\Aln\Socket\Messages;

final class EmptyFeederMessage extends IdentifiedMessage
{
    private int $hours;
    private int $minutes;
    private int $mealQuantity;

    public function __construct(string $hexadecimal)
    {
        $hexadecimalIdentifier = substr($hexadecimal, 6, -10);
        $hexadecimalTime = substr($hexadecimal, -8, 4);
        $hexadecimalMealAmount = substr($hexadecimal, -4);
        $this->identifier = $this->decodeIdentifier($hexadecimalIdentifier);
        ['hours' => $this->hours, 'minutes' => $this->minutes] = $this->decodeTime($hexadecimalTime);
        $this->mealQuantity = $this->decodeMealAmount($hexadecimalMealAmount);
    }

    /**
     * @return array<string, int>
     */
    public function getTime()
    {
        return ['hours' => $this->hours, 'minutes' => $this->minutes];
    }

    /**
     * @return-stan int<5, 150>
     */
    public function getMealQuantity(): int
    {
        return $this->mealQuantity;
    }
}
