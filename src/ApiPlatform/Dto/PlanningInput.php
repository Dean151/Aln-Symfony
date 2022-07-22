<?php

namespace App\ApiPlatform\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class PlanningInput
{
    /**
     * @var MealInput[]
     */
    #[Assert\Valid]
    public array $meals;

    /**
     * @param MealInput[] $meals
     */
    public function __construct(array $meals)
    {
        $this->meals = $meals;
    }
}
