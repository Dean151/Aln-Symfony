<?php

namespace App\Aln\Socket\Messages;

use App\Aln\Socket\MessageTranscriber;

final class TimeMessage implements MessageInterface
{
    use MessageTranscriber;

    /**
     * @var array{hours: int<0, 23>, minutes: int<0, 59>}
     */
    private array $time;

    public static function decodeFrom(string $hexadecimal): self
    {
        $hexadecimalTime = substr($hexadecimal, -4);
        $time = self::decodeTime($hexadecimalTime);

        return new TimeMessage($time);
    }

    /**
     * @param array{hours: int<0, 23>, minutes: int<0, 59>} $time
     */
    public function __construct(array $time)
    {
        $this->time = $time;
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
