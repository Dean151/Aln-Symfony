<?php

namespace App\Aln\Socket\Messages;

final class EmptyFeederMessage extends IdentifiedMessage
{
    /**
     * @var array{hours: int<0, 23>, minutes: int<0, 59>}
     */
    private array $time;

    /**
     * @var int<5, 150>
     */
    private int $mealAmount;

    public static function decodeFrom(string $hexadecimal): self
    {
        $hexadecimalIdentifier = substr($hexadecimal, 6, -10);
        $hexadecimalTime = substr($hexadecimal, -8, 4);
        $hexadecimalMealAmount = substr($hexadecimal, -4);
        $identifier = self::decodeIdentifier($hexadecimalIdentifier);
        $time = self::decodeTime($hexadecimalTime);
        $mealAmount = self::decodeMealAmount($hexadecimalMealAmount);

        return new EmptyFeederMessage($identifier, $mealAmount, $time);
    }

    /**
     * @param ?array{hours: int<0, 23>, minutes: int<0, 59>} $time
     * @param int<5, 150>                                    $mealAmount
     */
    public function __construct(string $identifier, int $mealAmount, ?array $time = null)
    {
        $this->time = $time ?? TimeMessage::now();
        $this->mealAmount = $mealAmount;
        parent::__construct($identifier);
    }

    /**
     * @return array{hours: int<0, 23>, minutes: int<0, 59>}
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return int<5, 150>
     */
    public function getMealAmount(): int
    {
        return $this->mealAmount;
    }

    public function hexadecimal(): string
    {
        $time = $this->encodeTime($this->time);
        $amount = $this->encodeMealAmount($this->mealAmount);

        return '9da114'.bin2hex($this->identifier).'21'.$time.$amount;
    }
}
