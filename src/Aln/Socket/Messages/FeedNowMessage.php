<?php

namespace App\Aln\Socket\Messages;

use App\Aln\Socket\MessageTranscriber;

final class FeedNowMessage implements OutgoingMessageInterface
{
    use MessageTranscriber;

    /**
     * @var int<5, 150>
     */
    private int $mealAmount;

    /**
     * @param int<5, 150> $mealAmount
     */
    public function __construct(int $mealAmount)
    {
        $this->mealAmount = $mealAmount;
    }

    public function hexadecimal(): string
    {
        $prefix = '9da106a2';
        $amount = $this->encodeMealAmount($this->mealAmount);

        return $prefix.$amount;
    }
}
