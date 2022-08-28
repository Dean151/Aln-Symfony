<?php

declare(strict_types=1);

namespace App\Socket;

use App\Entity\AlnAlert;
use App\Entity\AlnManualMeal;
use App\Queue\AbstractQueue;
use App\Queue\MessageDequeueInterface;
use App\Repository\AlnAlertRepository;
use App\Repository\AlnFeederRepository;
use App\Repository\AlnManualMealRepository;
use App\Socket\Messages\ExpectationMessage;
use App\Socket\Messages\IdentificationMessage;
use App\Socket\Messages\MealTriggeredViaButtonMessage;
use App\Socket\Messages\TimeMessage;
use Doctrine\Persistence\ManagerRegistry;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;
use Safe\DateTimeImmutable;

use function Safe\hex2bin;
use function Safe\parse_url;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

final class FeederCommunicator extends AbstractQueue implements MessageDequeueInterface, SocketMessageInterface
{
    private AlnFeederRepository $feederRepository;
    private AlnManualMealRepository $mealRepository;
    private AlnAlertRepository $alertRepository;
    private ManagerRegistry $doctrine;
    private LoggerInterface $logger;

    /**
     * @var array<string, ConnectionInterface>
     */
    private array $connections = [];

    public function __construct(
        ContainerBagInterface $params,
        AlnFeederRepository $feederRepository,
        AlnManualMealRepository $mealRepository,
        AlnAlertRepository $alertRepository,
        ManagerRegistry $doctrine,
        LoggerInterface $logger
    ) {
        $this->feederRepository = $feederRepository;
        $this->mealRepository = $mealRepository;
        $this->alertRepository = $alertRepository;
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        parent::__construct($params);
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->logger->info('New connection opened');
    }

    public function onClose(ConnectionInterface $conn): void
    {
        if ($identifier = array_search($conn, $this->connections, true)) {
            $this->logger->info("connection closed with feeder $identifier");
            unset($this->connections[$identifier]);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $conn->close();
    }

    public function onData(ConnectionInterface $from, string $data): void
    {
        $hexadecimalMessage = bin2hex($data);
        $this->logger->debug("Data received: {$hexadecimalMessage}");
        try {
            $message = MessageIdentification::identifyIncomingMessage($hexadecimalMessage);
            if ($message instanceof IdentificationMessage) {
                $this->identified($message, $from);
            } elseif ($message instanceof MealTriggeredViaButtonMessage) {
                $this->recordManualMeal($message);
            } elseif ($message instanceof ExpectationMessage) {
                $this->publishResponseInQueue($message);
            }
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);

            $alert = new AlnAlert();
            if ($ip = $this->getIpFrom($from)) {
                $alert->setIp($ip);
            }
            $alert->setMessage($e->getMessage());
            if ($identifier = array_search($from, $this->connections, true)) {
                $feeder = $this->feederRepository->findOneByIdentifier($identifier);
                $alert->setFeeder($feeder);
            } else {
                // Only close a connexion that send unknown data and never identify itself
                $from->close();
            }
            $this->alertRepository->add($alert);
        }
    }

    public function dequeueMessage(AMQPMessage $amqpMessage): void
    {
        [$identifier, $hexadecimal] = explode('|', $amqpMessage->getBody());
        $this->sendInSocket($hexadecimal, $identifier);
    }

    private function publishResponseInQueue(ExpectationMessage $message): void
    {
        $ampqMessage = new AMQPMessage($message->hexadecimal());

        $connection = $this->getQueueConnection();
        $channel = $connection->channel();

        $this->publishInQueue($channel, self::QUEUE_RESPONSE, $ampqMessage);

        $channel->close();
        $connection->close();
    }

    private function persist(ConnectionInterface $connection, string $identifier): void
    {
        $this->connections[$identifier] = $connection;
    }

    private function find(string $identifier): ?ConnectionInterface
    {
        return $this->connections[$identifier] ?? null;
    }

    private function sendInSocket(string $hexadecimal, string $identifier): bool
    {
        $this->logger->debug("Sending to $identifier: ".$hexadecimal);
        $connection = $this->find($identifier);
        if (!$connection instanceof ConnectionInterface) {
            $this->logger->warning("No connection for $identifier");

            return false;
        }

        $connection->write(hex2bin($hexadecimal));

        return true;
    }

    private function identified(IdentificationMessage $message, ConnectionInterface $connection): void
    {
        $this->logger->info("Feeder identified with {$message->getIdentifier()}");
        $this->persist($connection, $message->getIdentifier());

        $feeder = $this->feederRepository->findOrCreateFeeder($message->getIdentifier());
        $feeder->setLastSeen(new DateTimeImmutable('now'));
        if ($ip = $this->getIpFrom($connection)) {
            $feeder->setIp($ip);
        }

        $this->sendInSocket((new TimeMessage())->hexadecimal(), $message->getIdentifier());

        $this->doctrine->getManager()->flush();
    }

    private function recordManualMeal(MealTriggeredViaButtonMessage $message): void
    {
        $this->logger->info("Feeder {$message->getIdentifier()} served a meal of {$message->getMealAmount()}g");

        $feeder = $this->feederRepository->findOrCreateFeeder($message->getIdentifier());
        $feeder->setDefaultMealAmount($message->getMealAmount());

        $now = new DateTimeImmutable('now');
        $meal = new AlnManualMeal();
        $meal->setDistributedOn($now);
        $meal->setPreviousMeal($message->getPreviousMeal()->toArray());
        $feeder->addManualMeal($meal);
        $this->mealRepository->add($meal);

        $this->doctrine->getManager()->flush();
        // TODO: send push?
    }

    private function getIpFrom(ConnectionInterface $connection): ?string
    {
        if (!$address = $connection->getRemoteAddress()) {
            return null;
        }
        $host = parse_url($address, PHP_URL_HOST);
        if (!is_string($host)) {
            return null;
        }

        return trim($host, '[]');
    }
}
