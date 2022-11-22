<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\AlnFeeder;
use App\Queue\MessageEnqueueInterface;
use App\Socket\Messages\ChangeDefaultMealMessage;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ChangeDefaultMeal extends AbstractSocketController
{
    public function __construct(
        #[Autowire('%env(float:FEEDER_RESPONSE_TIMEOUT)%')]
        float $timeout,
        MessageEnqueueInterface $queue,
        private readonly ValidatorInterface $validator,
        private readonly ManagerRegistry $doctrine,
    ) {
        parent::__construct($timeout, $queue);
    }

    public function __invoke(AlnFeeder $data): Response
    {
        $this->validator->validate($data, ['groups' => ['feeding:validation']]);

        $amount = $data->amount;
        assert($amount >= 5 && $amount <= 150);
        $feeder = $data;

        $message = new ChangeDefaultMealMessage($amount);
        $this->sendSocketMessage($feeder, $message);

        $feeder->setDefaultMealAmount($amount);
        $this->doctrine->getManager()->flush();

        return $this->json([
            'message' => "{$amount}g meal is now the default amount",
        ]);
    }
}
