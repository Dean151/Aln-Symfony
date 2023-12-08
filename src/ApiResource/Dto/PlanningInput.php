<?php

declare(strict_types=1);

namespace App\ApiResource\Dto;

use App\Entity\AlnFeeder;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class PlanningInput
{
    public AlnFeeder $feeder;

    /**
     * @var MealInput[]
     */
    #[Groups('planning:input')]
    #[Assert\Valid]
    public array $meals;
}
