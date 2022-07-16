<?php

namespace App\Aln\Socket\Messages;

use App\Aln\Socket\MessageTranscriber;

final class TimeMessage implements OutgoingMessageInterface
{
    use MessageTranscriber;

    /**
     * @var int<0, 23>
     */
    private int $hours;

    /**
     * @var int<0, 59>
     */
    private int $minutes;

    /**
     * @param int<0, 23> $hours
     * @param int<0, 59> $minutes
     */
    public function __construct(int $hours, int $minutes)
    {
        $this->hours = $hours;
        $this->minutes = $minutes;
    }

    public function hexadecimal(): string
    {
        $prefix = '9da10601';
        $time = $this->encodeTime($this->hours, $this->minutes);

        return $prefix.$time;
    }
}
