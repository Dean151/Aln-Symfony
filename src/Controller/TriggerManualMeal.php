<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\AlnFeeder;
use App\Entity\AlnManualMeal;
use App\Queue\MessageEnqueueInterface;
use App\Repository\AlnManualMealRepository;
use App\Socket\Messages\TriggerMealMessage;
use Doctrine\Persistence\ManagerRegistry;
use Safe\DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class TriggerManualMeal extends AbstractSocketController
{
    public function __construct(
        #[Autowire('%env(float:FEEDER_RESPONSE_TIMEOUT)%')]
        float $timeout,
        MessageEnqueueInterface $queue,
        private readonly ValidatorInterface $validator,
        private readonly AlnManualMealRepository $repository,
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

        $message = new TriggerMealMessage($amount);
        $this->sendSocketMessage($feeder, $message);

        $meal = new AlnManualMeal();
        $meal->setDistributedOn(new DateTimeImmutable('now'));
        $meal->setAmount($amount);
        $feeder->addManualMeal($meal);
        $this->repository->add($meal);

        $this->doctrine->getManager()->flush();

        return $this->json([
            'message' => "{$amount}g meal has been distributed",
        ]);
    }
}
