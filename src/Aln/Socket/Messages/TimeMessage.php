<?php

namespace App\Aln\Socket\Messages;

use App\Aln\Socket\MessageTranscriber;
use Safe\DateTimeImmutable;

final class TimeMessage implements MessageInterface
{
    use MessageTranscriber;

    /**
     * @var array{hours: int<0, 23>, minutes: int<0, 59>}
     */
    private array $time;

    /**
     * @return array{hours: int<0, 23>, minutes: int<0, 59>}
     */
    public static function now(): array
    {
        $currentDatetime = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $hours = (int) $currentDatetime->format('H');
        $minutes = (int) $currentDatetime->format('i');
        assert($minutes >= 0 && $minutes < 60);

        return ['hours' => $hours, 'minutes' => $minutes];
    }

    public static function decodeFrom(string $hexadecimal): self
    {
        $hexadecimalTime = substr($hexadecimal, -4);
        $time = self::decodeTime($hexadecimalTime);

        return new TimeMessage($time);
    }

    /**
     * @param ?array{hours: int<0, 23>, minutes: int<0, 59>} $time
     */
    public function __construct(?array $time)
    {
        $this->time = $time ?? TimeMessage::now();
    }

    /**
     * @return array{hours: int<0, 23>, minutes: int<0, 59>}
     */
    public function getTime(): array
    {
        return $this->time;
    }

    public function hexadecimal(): string
    {
        $prefix = '9da10601';
        $time = $this->encodeTime($this->time);

        return $prefix.$time;
    }
}
