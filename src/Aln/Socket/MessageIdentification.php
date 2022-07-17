<?php

namespace App\Aln\Socket;

use App\Aln\Socket\Messages\DefaultMealChangedMessage;
use App\Aln\Socket\Messages\EmptyFeederMessage;
use App\Aln\Socket\Messages\IdentificationMessage;
use App\Aln\Socket\Messages\MealButtonPressedMessage;
use App\Aln\Socket\Messages\MealDistributedMessage;
use App\Aln\Socket\Messages\MessageInterface;
use App\Aln\Socket\Messages\PlanningChangedMessage;
use Safe\Exceptions\PcreException;

use function Safe\preg_match;

final class MessageIdentification
{
    /**
     * @throws PcreException|\RuntimeException
     */
    public static function identifyIncomingMessage(string $hexadecimal): MessageInterface
    {
        if (!str_starts_with($hexadecimal, '9da114')) {
            throw new \RuntimeException('All incoming messages should start with 9da114');
        }
        if (str_ends_with($hexadecimal, '01d0010000')) {
            return IdentificationMessage::decodeFrom($hexadecimal);
        }
        if (str_ends_with($hexadecimal, 'c3d0a10000')) {
            return DefaultMealChangedMessage::decodeFrom($hexadecimal);
        }
        if (str_ends_with($hexadecimal, 'c4d0a10000')) {
            return PlanningChangedMessage::decodeFrom($hexadecimal);
        }
        if (str_ends_with($hexadecimal, 'a2d0a10000')) {
            return MealDistributedMessage::decodeFrom($hexadecimal);
        }
        if (preg_match('/21038400([0-9a-f]{2})$/', $hexadecimal, $matches)) {
            return MealButtonPressedMessage::decodeFrom($hexadecimal);
        }
        if (preg_match('/210(?:5\d[[:xdigit:]]|[0-4][[:xdigit:]]{2})(00[0-9][0-9a-f])$/', $hexadecimal, $matches)) {
            return EmptyFeederMessage::decodeFrom($hexadecimal);
        }
        throw new \RuntimeException('Unknown incoming message: '.$hexadecimal);
    }
}
