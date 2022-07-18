<?php

namespace App\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class MealInput
{
    public TimeInput $time;

    /**
     * @var int<5, 150>
     */
    #[Assert\Range(min: 5, max: 150, groups: ['planning:validation'])]
    public int $amount;

    public bool $isEnabled;

    /**
     * @param int<5, 150> $amount
     */
    public function __construct(TimeInput $time, int $amount, bool $isEnabled = true)
    {
        $this->time = $time;
        $this->amount = $amount;
        $this->isEnabled = $isEnabled;
    }
}
