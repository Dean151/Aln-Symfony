<?php

namespace App\Aln\Socket\Messages;

use App\Aln\Socket\MessageTranscriber;

final class ChangeDefaultMealMessage implements MessageInterface
{
    use MessageTranscriber;

    /**
     * @var int<5, 150>
     */
    private int $mealAmount;

    public static function decodeFrom(string $hexadecimal): self
    {
        $hexadecimalMealAmount = substr($hexadecimal, -4);
        $mealAmount = self::decodeMealAmount($hexadecimalMealAmount);

        return new ChangeDefaultMealMessage($mealAmount);
    }

    /**
     * @param int<5, 150> $mealAmount
     */
    public function __construct(int $mealAmount)
    {
        $this->mealAmount = $mealAmount;
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
        $prefix = '9da106c3';
        $amount = $this->encodeMealAmount($this->mealAmount);

        return $prefix.$amount;
    }
}
