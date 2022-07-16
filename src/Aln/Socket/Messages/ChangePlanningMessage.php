<?php

namespace App\Aln\Socket\Messages;

use App\Aln\Socket\MessageTranscriber;

class ChangePlanningMessage implements OutgoingMessageInterface
{
    use MessageTranscriber;

    /**
     * @var array<array{time: array{hours: int<0, 23>, minutes: int<0, 59>}, amount: int<5, 150>}>
     */
    private array $meals;

    /**
     * @param array<array{time: array{hours: int<0, 23>, minutes: int<0, 59>}, amount: int<5, 150>}> $meals
     */
    public function __construct(array $meals)
    {
        $this->meals = $meals;
    }

    public function hexadecimal(): string
    {
        $prefix = '9da12dc4';
        $planning = $this->encodePlanning($this->meals);

        return $prefix.$planning;
    }
}
