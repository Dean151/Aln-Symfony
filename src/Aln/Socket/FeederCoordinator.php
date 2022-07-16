<?php

namespace App\Aln\Socket;

use App\Aln\Socket\Messages\EmptyFeederMessage;
use App\Aln\Socket\Messages\IdentificationMessage;
use App\Aln\Socket\Messages\MealButtonPressedMessage;
use App\Entity\AlnMeal;
use App\Repository\AlnFeederRepository;
use App\Repository\AlnMealRepository;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Safe\DateTimeImmutable;

use function Safe\hex2bin;

final class FeederCoordinator
{
    private AlnFeederRepository $feederRepository;
    private AlnMealRepository $mealRepository;
    private MessageFactory $messageFactory;
    private LoggerInterface $logger;

    public function __construct(
        AlnFeederRepository $feederRepository,
        AlnMealRepository $mealRepository,
        MessageFactory $messageFactory,
        LoggerInterface $logger
    ) {
        $this->feederRepository = $feederRepository;
        $this->mealRepository = $mealRepository;
        $this->messageFactory = $messageFactory;
        $this->logger = $logger;
    }

    public function handleSocketMessage(ConnectionInterface $connection, string $hexadecimal): void
    {
        $message = $this->messageFactory->identifyIncoming($hexadecimal);
        if ($message instanceof IdentificationMessage) {
            $this->identify($message, $connection);
        } elseif ($message instanceof MealButtonPressedMessage) {
            $this->recordManualMeal($message);
        } elseif ($message instanceof EmptyFeederMessage) {
            $this->recordEmptyFeeder($message);
        }
    }

    private function identify(IdentificationMessage $message, ConnectionInterface $connection): void
    {
        $this->logger->info("Feeder identified with {$message->getIdentifier()}");

        $feeder = $this->feederRepository->findOrCreateFeeder($message->getIdentifier());
        $feeder->setLastSeen(new DateTimeImmutable());

        $time = $this->messageFactory->currentTime();
        $connection->send(hex2bin($time->hexadecimal()));

        // FIXME: flush?
    }

    private function recordManualMeal(MealButtonPressedMessage $message): void
    {
        $this->logger->info("Feeder {$message->getIdentifier()} served a meal of {$message->getMealQuantity()}g");

        $feeder = $this->feederRepository->findOrCreateFeeder($message->getIdentifier());
        $feeder->setDefaultMealAmount($message->getMealQuantity());

        $now = new DateTimeImmutable();
        $meal = new AlnMeal();
        $meal->setDate($now);
        $meal->setTime($now);
        $meal->setFeeder($feeder);
        $this->mealRepository->add($meal);

        // TODO: send push?
        // FIXME: flush?
    }

    private function recordEmptyFeeder(EmptyFeederMessage $message): void
    {
        $this->logger->info("Feeder {$message->getIdentifier()} is empty");

        // TODO: Register information, send push?
    }
}
