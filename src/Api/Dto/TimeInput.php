<?php

namespace App\Api\Dto;

use Safe\DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

final class TimeInput
{
    /**
     * @var int<0, 23>
     */
    #[Assert\Range(min: 0, max: 23)]
    public int $hours;

    /**
     * @var int<0, 59>
     */
    #[Assert\Range(min: 0, max: 59)]
    public int $minutes;

    /**
     * @param int<0, 23> $hours
     * @param int<0, 59> $minutes
     */
    public function __construct(int $hours, int $minutes)
    {
        $this->hours = $hours;
        $this->minutes = $minutes;
    }

    public static function now(): TimeInput
    {
        $currentDatetime = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $hours = (int) $currentDatetime->format('H');
        $minutes = (int) $currentDatetime->format('i');
        assert($minutes >= 0 && $minutes < 60);

        return new TimeInput($hours, $minutes);
    }
}
