<?php

namespace App\Aln\Socket;

use App\Aln\Socket\Messages\EmptyFeederMessage;
use App\Aln\Socket\Messages\IdentificationMessage;
use App\Aln\Socket\Messages\MealButtonPressedMessage;
use App\Aln\Socket\Messages\MessageInterface;
use App\Entity\AlnFeeder;
use App\Entity\AlnMeal;
use App\Repository\AlnFeederRepository;
use App\Repository\AlnMealRepository;
use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\Frame;
use Safe\DateTimeImmutable;
use Safe\Exceptions\StringsException;

use function Safe\hex2bin;

final class FeederCommunicator implements MessageDequeueInterface, MessageEnqueueInterface
{
    private AlnFeederRepository $feederRepository;
    private AlnMealRepository $mealRepository;
    private MessageFactory $messageFactory;
    private ManagerRegistry $doctrine;
    private LoggerInterface $logger;

    /**
     * @var array<string, ConnectionInterface>
     */
    private array $connections = [];

    public function __construct(
        AlnFeederRepository $feederRepository,
        AlnMealRepository $mealRepository,
        MessageFactory $messageFactory,
        ManagerRegistry $doctrine,
        LoggerInterface $logger
    ) {
        $this->feederRepository = $feederRepository;
        $this->mealRepository = $mealRepository;
        $this->messageFactory = $messageFactory;
        $this->doctrine = $doctrine;
        $this->logger = $logger;
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->logger->info('New connexion opened');
    }

    public function onClose(ConnectionInterface $conn): void
    {
        if ($identifier = array_search($conn, $this->connections, true)) {
            $this->logger->info("Connexion closed with feeder $identifier");
            unset($this->connections[$identifier]);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $hexadecimalMessage = bin2hex($msg);
        $this->logger->debug("Data received: {$hexadecimalMessage}");
        try {
            $message = $this->messageFactory->identifyIncoming($hexadecimalMessage);
            if ($message instanceof IdentificationMessage) {
                $this->identify($message, $from);
            } elseif ($message instanceof MealButtonPressedMessage) {
                $this->recordManualMeal($message);
            } elseif ($message instanceof EmptyFeederMessage) {
                $this->recordEmptyFeeder($message);
            }
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
            $from->close();
        }
    }

    /**
     * @throws StringsException|\Exception
     */
    public function enqueueMessage(AlnFeeder $feeder, MessageInterface $message): void
    {
        $queueClient = new Client([
            'host' => $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['RABBITMQ_PORT'] ?? 5672,
            'vhost' => $_ENV['RABBITMQ_VHOST'] ?? '/',
            'user' => $_ENV['RABBITMQ_USERNAME'] ?? 'guest',
            'password' => $_ENV['RABBITMQ_PASSWORD'] ?? 'guest',
        ]);
        $queue = $_ENV['RABBITMQ_ALN_QUEUE'] ?? 'aln';
        $connect = $queueClient->connect();
        $channel = $connect->channel();
        assert($channel instanceof Channel);
        $channel->queueDeclare($queue);
        $channel->publish(hex2bin($message->hexadecimal()), ['identifier' => $feeder->getIdentifier()], '', $queue, true, true);
    }

    public function dequeueMessage(Message $message): void
    {
        $identifier = $message->getHeader('identifier');
        if (!is_string($identifier)) {
            $this->logger->error('Missing identifier in Queue Message');

            return;
        }
        $connection = $this->find($identifier);
        if (!$connection instanceof ConnectionInterface) {
            $this->logger->warning("No connection for identifier: $identifier");

            return;
        }

        $connection->send($message->content);
    }

    private function persist(ConnectionInterface $connection, string $identifier): void
    {
        $this->connections[$identifier] = $connection;
    }

    private function find(string $identifier): ?ConnectionInterface
    {
        return $this->connections[$identifier] ?? null;
    }

    private function send(MessageInterface $message, string $identifier): void
    {
        $this->logger->debug("Sending to $identifier: ".$message->hexadecimal());
        $frame = new Frame(hex2bin($message->hexadecimal()), true, Frame::OP_BINARY);
        $connexion = $this->find($identifier);
        if ($connexion instanceof ConnectionInterface) {
            $connexion->send($frame);
        } else {
            $this->logger->warning("Not found connexion for $identifier");
        }
    }

    private function identify(IdentificationMessage $message, ConnectionInterface $connection): void
    {
        $this->logger->info("Feeder identified with {$message->getIdentifier()}");
        $this->persist($connection, $message->getIdentifier());

        $feeder = $this->feederRepository->findOrCreateFeeder($message->getIdentifier());
        $feeder->setLastSeen(new DateTimeImmutable());

        $time = $this->messageFactory->currentTime();
        $this->send($time, $message->getIdentifier());

        $this->doctrine->getManager()->flush();
    }

    private function recordManualMeal(MealButtonPressedMessage $message): void
    {
        $this->logger->info("Feeder {$message->getIdentifier()} served a meal of {$message->getMealAmount()}g");

        $feeder = $this->feederRepository->findOrCreateFeeder($message->getIdentifier());
        $feeder->setDefaultMealAmount($message->getMealAmount());

        $now = new DateTimeImmutable();
        $meal = new AlnMeal();
        $meal->setDate($now);
        $meal->setTime($now);
        $meal->setFeeder($feeder);
        $this->mealRepository->add($meal);

        $this->doctrine->getManager()->flush();
        // TODO: send push?
    }

    private function recordEmptyFeeder(EmptyFeederMessage $message): void
    {
        $this->logger->info("Feeder {$message->getIdentifier()} is empty");

        // TODO: Register information, send push?
    }
}
