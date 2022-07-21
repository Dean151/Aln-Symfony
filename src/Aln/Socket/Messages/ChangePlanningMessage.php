<?php

namespace App\Aln\Socket\Messages;

use App\Aln\Socket\MessageTranscriber;
use App\Api\Dto\MealInput;

class ChangePlanningMessage implements ExpectableMessageInterface
{
    use MessageTranscriber;

    /**
     * @var array<MealInput>
     */
    private array $meals;

    public static function decodeFrom(string $hexadecimal): self
    {
        $hexadecimalPlanning = substr($hexadecimal, 8);
        $meals = self::decodePlanning($hexadecimalPlanning);

        return new ChangePlanningMessage($meals);
    }

    /**
     * @param array<MealInput> $meals
     */
    public function __construct(array $meals)
    {
        $this->meals = $meals;
    }

    /**
     * @return array<MealInput>
     */
    public function getMeals(): array
    {
        return $this->meals;
    }

    public function getCount(): int
    {
        return count($this->meals);
    }

    public function hexadecimal(): string
    {
        $prefix = '9da12dc4';
        $planning = $this->encodePlanning($this->meals);

        return $prefix.$planning;
    }

    public function expectationMessage(string $identifier): ExpectationMessage
    {
        return new PlanningChangedMessage($identifier);
    }
}
