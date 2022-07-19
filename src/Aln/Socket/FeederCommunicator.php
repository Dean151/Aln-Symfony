<?php

namespace App\Aln\Socket;

use App\Aln\Socket\Messages\EmptyFeederMessage;
use App\Aln\Socket\Messages\ExpectationMessage;
use App\Aln\Socket\Messages\IdentificationMessage;
use App\Aln\Socket\Messages\MealButtonPressedMessage;
use App\Aln\Socket\Messages\TimeMessage;
use App\Entity\AlnMeal;
use App\Repository\AlnFeederRepository;
use App\Repository\AlnMealRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\Frame;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

use function React\Promise\Timer\timeout;

use Safe\DateTimeImmutable;

use function Safe\hex2bin;

final class FeederCommunicator implements MessageDequeueInterface
{
    private AlnFeederRepository $feederRepository;
    private AlnMealRepository $mealRepository;
    private ManagerRegistry $doctrine;
    private LoggerInterface $logger;

    /**
     * @var array<string, ConnectionInterface>
     */
    private array $connections = [];

    /**
     * @var array<string, int>
     */
    private array $completedExpectations = [];

    public function __construct(
        AlnFeederRepository $feederRepository,
        AlnMealRepository $mealRepository,
        ManagerRegistry $doctrine,
        LoggerInterface $logger
    ) {
        $this->feederRepository = $feederRepository;
        $this->mealRepository = $mealRepository;
        $this->doctrine = $doctrine;
        $this->logger = $logger;
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

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $hexadecimalMessage = bin2hex($msg);
        $this->logger->debug("Data received: {$hexadecimalMessage}");
        try {
            $message = MessageIdentification::identifyIncomingMessage($hexadecimalMessage);
            if ($message instanceof IdentificationMessage) {
                $this->identified($message, $from);
            } elseif ($message instanceof MealButtonPressedMessage) {
                $this->recordManualMeal($message);
            } elseif ($message instanceof EmptyFeederMessage) {
                $this->recordEmptyFeeder($message);
            } elseif ($message instanceof ExpectationMessage) {
                $this->completedExpectations[$message->hexadecimal()] = time();
            }
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
            $from->close();
        }
    }

    public function dequeueMessageAndWait(AMQPMessage $message, LoopInterface $loop, float $timeout = 5): PromiseInterface
    {
        [$identifier, $hexadecimal] = explode('|', $message->getBody());
        $expectation = MessageIdentification::findExpectedResponseMessage($hexadecimal, $identifier);
        if (!$this->sendInSocket($hexadecimal, $identifier)) {
            return new Promise(function ($resolve, $reject) {
                $reject();
            });
        }
        if (null === $expectation) {
            return new Promise(function ($resolve) {
                $resolve();
            });
        }

        $promise = new Promise(function ($resolve) use ($loop, $expectation, &$callback) {
            $callback = function () use ($resolve, $loop, $expectation, &$callback) {
                if (isset($this->completedExpectations[$expectation->hexadecimal()])) {
                    $time = $this->completedExpectations[$expectation->hexadecimal()];
                    if (time() - $time < 5) {
                        unset($this->completedExpectations[$expectation->hexadecimal()]);
                        $resolve();
                    }
                }
                $loop->addTimer(0.5, $callback);
            };
            $loop->addTimer(0.5, $callback);
        }, function () use (&$callback) {
            $callback = function () {};
        });

        return timeout($promise, $timeout, $loop);
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
        $frame = new Frame(hex2bin($hexadecimal), true, Frame::OP_BINARY);
        $connection = $this->find($identifier);
        if (!$connection instanceof ConnectionInterface) {
            $this->logger->warning("No connection for $identifier");

            return false;
        }

        $connection->send($frame);

        return true;
    }

    private function identified(IdentificationMessage $message, ConnectionInterface $connection): void
    {
        $this->logger->info("Feeder identified with {$message->getIdentifier()}");
        $this->persist($connection, $message->getIdentifier());

        $feeder = $this->feederRepository->findOrCreateFeeder($message->getIdentifier());
        $feeder->setLastSeen(new DateTimeImmutable('now', new \DateTimeZone('UTC')));

        $this->sendInSocket((new TimeMessage())->hexadecimal(), $message->getIdentifier());

        $this->doctrine->getManager()->flush();
    }

    private function recordManualMeal(MealButtonPressedMessage $message): void
    {
        $this->logger->info("Feeder {$message->getIdentifier()} served a meal of {$message->getMealAmount()}g");

        $feeder = $this->feederRepository->findOrCreateFeeder($message->getIdentifier());
        $feeder->setDefaultMealAmount($message->getMealAmount());

        $now = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $meal = new AlnMeal();
        $meal->setDistributedOn($now);
        $feeder->addMeal($meal);
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
