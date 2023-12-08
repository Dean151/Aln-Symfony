<?php

declare(strict_types=1);

namespace App\ApiResource\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class MealInput
{
    #[Groups('planning:input')]
    #[Assert\Valid]
    public TimeInput $time;

    /**
     * @var int<5, 150>
     */
    #[Groups('planning:input')]
    #[Assert\Range(min: 5, max: 150)]
    public int $amount;

    #[Groups('planning:input')]
    #[SerializedName('enabled')]
    public bool $isEnabled = true;

    /**
     * @phpstan-param int<5, 150> $amount
     */
    public function __construct(TimeInput $time, int $amount, bool $isEnabled = true)
    {
        $this->time = $time;
        $this->amount = $amount;
        $this->isEnabled = $isEnabled;
    }
}
