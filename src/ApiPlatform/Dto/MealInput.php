<?php

declare(strict_types=1);

namespace App\ApiPlatform\Dto;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class MealInput
{
    #[Assert\Valid]
    public TimeInput $time;

    /**
     * @var int<5, 150>
     */
    #[Assert\Range(min: 5, max: 150)]
    public int $amount;

    #[SerializedName('enabled')]
    public bool $isEnabled = true;

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
