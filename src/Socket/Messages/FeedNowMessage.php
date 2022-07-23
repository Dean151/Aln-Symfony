<?php

declare(strict_types=1);

namespace App\Socket\Messages;

use App\Socket\MessageTranscriber;

final class FeedNowMessage extends ExpectableMessageInterface
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

        return new FeedNowMessage($mealAmount);
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
        $prefix = '9da106a2';
        $amount = $this->encodeMealAmount($this->mealAmount);

        return $prefix.$amount;
    }

    public function expectationMessage(string $identifier): ExpectationMessage
    {
        return new MealDistributedMessage($identifier);
    }
}
