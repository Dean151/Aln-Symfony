<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AlnFeeder;
use App\Queue\MessageEnqueueInterface;
use App\Socket\Messages\ExpectableMessageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

abstract class AbstractSocketController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(float:FEEDER_RESPONSE_TIMEOUT)%')]
        private readonly float $timeout,
        protected readonly MessageEnqueueInterface $queue,
    ) {
    }

    protected function sendSocketMessage(AlnFeeder $feeder, ExpectableMessageInterface $message): void
    {
        $identifier = $feeder->getIdentifier();
        if (!$feeder->isAvailable() || !is_string($identifier)) {
            throw new ConflictHttpException('Feeder is not available');
        }
        $feederResponded = $this->queue->enqueueSocketMessageAndWait($feeder, $message, $this->timeout);
        if (!$feederResponded) {
            throw new ServiceUnavailableHttpException(null, 'Feeder did not responded in time.');
        }
    }
}
