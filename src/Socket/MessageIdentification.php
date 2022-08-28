<?php

declare(strict_types=1);

namespace App\Socket;

use App\Socket\Messages\ChangeDefaultMealMessage;
use App\Socket\Messages\ChangePlanningMessage;
use App\Socket\Messages\DefaultMealChangedMessage;
use App\Socket\Messages\IdentificationMessage;
use App\Socket\Messages\MealTriggeredViaButtonMessage;
use App\Socket\Messages\MealTriggeredViaNetworkMessage;
use App\Socket\Messages\MessageInterface;
use App\Socket\Messages\PlanningChangedMessage;
use App\Socket\Messages\TimeMessage;
use App\Socket\Messages\TriggerMealMessage;
use Safe\Exceptions\PcreException;

use function Safe\preg_match;

final class MessageIdentification
{
    /**
     * @throws PcreException|\RuntimeException
     */
    public static function identifyIncomingMessage(string $hexadecimal): MessageInterface
    {
        if (!\str_starts_with($hexadecimal, '9da114')) {
            throw new \RuntimeException("All incoming messages should start with 9da114 ; got {$hexadecimal}");
        }
        if (\str_ends_with($hexadecimal, '01d0010000')) {
            return IdentificationMessage::decodeFrom($hexadecimal);
        }
        if (\str_ends_with($hexadecimal, 'c3d0a10000')) {
            return DefaultMealChangedMessage::decodeFrom($hexadecimal);
        }
        if (\str_ends_with($hexadecimal, 'c4d0a10000')) {
            return PlanningChangedMessage::decodeFrom($hexadecimal);
        }
        if (\str_ends_with($hexadecimal, 'a2d0a10000')) {
            return MealTriggeredViaNetworkMessage::decodeFrom($hexadecimal);
        }
        if (preg_match('/210(?:5\d[[:xdigit:]]|[0-4][[:xdigit:]]{2})(00[0-9][0-9a-f])$/', $hexadecimal, $matches)) {
            return MealTriggeredViaButtonMessage::decodeFrom($hexadecimal);
        }
        throw new \RuntimeException("Unknown incoming message ; got {$hexadecimal}");
    }

    public static function identifyOutgoingMessage(string $hexadecimal): MessageInterface
    {
        if (\str_starts_with($hexadecimal, '9da10601')) {
            return TimeMessage::decodeFrom($hexadecimal);
        }
        if (\str_starts_with($hexadecimal, '9da106c3')) {
            return ChangeDefaultMealMessage::decodeFrom($hexadecimal);
        }
        if (\str_starts_with($hexadecimal, '9da106a2')) {
            return TriggerMealMessage::decodeFrom($hexadecimal);
        }
        if (\str_starts_with($hexadecimal, '9da12dc4')) {
            return ChangePlanningMessage::decodeFrom($hexadecimal);
        }
        throw new \RuntimeException('Unknown outgoing message: '.$hexadecimal);
    }
}
