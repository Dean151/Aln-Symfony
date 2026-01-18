<?php

declare(strict_types=1);

namespace App\ApiResource\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
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
    #[ApiProperty(example: 5)]
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
