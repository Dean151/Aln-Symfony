<?php

declare(strict_types=1);

namespace App\ApiPlatform\Dto;

use Safe\DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class TimeInput
{
    /**
     * @var int<0, 23>
     */
    #[Groups('planning:input')]
    #[Assert\Range(min: 0, max: 23)]
    public int $hours;

    /**
     * @var int<0, 59>
     */
    #[Groups('planning:input')]
    #[Assert\Range(min: 0, max: 59)]
    public int $minutes;

    /**
     * @phpstan-param int<0, 23> $hours
     * @phpstan-param int<0, 59> $minutes
     */
    public function __construct(int $hours, int $minutes)
    {
        $this->hours = $hours;
        $this->minutes = $minutes;
    }

    public static function now(): TimeInput
    {
        $currentDatetime = new DateTimeImmutable('now');
        $hours = (int) $currentDatetime->format('H');
        $minutes = (int) $currentDatetime->format('i');
        assert($minutes >= 0 && $minutes < 60);

        return new TimeInput($hours, $minutes);
    }

    /**
     * @return array{hours: int<0, 23>, minutes: int<0, 59>}
     */
    public function toArray(): array
    {
        return ['hours' => $this->hours, 'minutes' => $this->minutes];
    }
}
