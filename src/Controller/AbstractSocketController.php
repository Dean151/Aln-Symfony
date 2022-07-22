<?php

namespace App\Controller;

use App\Entity\AlnFeeder;
use App\Queue\MessageEnqueueInterface;
use App\Socket\Messages\ExpectableMessageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

abstract class AbstractSocketController extends AbstractController
{
    protected MessageEnqueueInterface $queue;

    public function __construct(MessageEnqueueInterface $queue)
    {
        $this->queue = $queue;
    }

    protected function sendSocketMessage(AlnFeeder $feeder, ExpectableMessageInterface $message): void
    {
        $identifier = $feeder->getIdentifier();
        if (!$feeder->isAvailable() || !is_string($identifier)) {
            throw new ConflictHttpException('Feeder is not available');
        }
        $timeout = (float) $this->getParameter('feeder.response_timeout');
        $feederResponded = $this->queue->enqueueSocketMessageAndWait($feeder, $message, $timeout);
        if (!$feederResponded) {
            throw new ServiceUnavailableHttpException(null, 'Feeder did not responded in time.');
        }
    }
}
