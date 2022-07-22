<?php

namespace App\Socket\Messages;

use App\ApiPlatform\Dto\TimeInput;
use App\Socket\MessageTranscriber;

final class TimeMessage implements MessageInterface
{
    use MessageTranscriber;

    private TimeInput $time;

    public static function decodeFrom(string $hexadecimal): self
    {
        $hexadecimalTime = substr($hexadecimal, -4);
        $time = self::decodeTime($hexadecimalTime);

        return new TimeMessage($time);
    }

    public function __construct(?TimeInput $time = null)
    {
        $this->time = $time ?? TimeInput::now();
    }

    public function getTime(): TimeInput
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
