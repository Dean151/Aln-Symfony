<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\AlnFeeder;
use App\Entity\AlnManualMeal;
use App\Queue\MessageEnqueueInterface;
use App\Repository\AlnManualMealRepository;
use App\Socket\Messages\FeedNowMessage;
use Doctrine\Persistence\ManagerRegistry;
use Safe\DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class FeedNowController extends AbstractSocketController
{
    private ValidatorInterface $validator;
    private AlnManualMealRepository $repository;
    private ManagerRegistry $doctrine;

    public function __construct(
        MessageEnqueueInterface $queue,
        ValidatorInterface $validator,
        AlnManualMealRepository $repository,
        ManagerRegistry $doctrine
    ) {
        $this->validator = $validator;
        $this->repository = $repository;
        $this->doctrine = $doctrine;
        parent::__construct($queue);
    }

    public function __invoke(AlnFeeder $data): Response
    {
        $this->validator->validate($data, ['groups' => ['feeding:validation']]);

        $amount = $data->amount;
        assert($amount >= 5 && $amount <= 150);
        $feeder = $data;

        $message = new FeedNowMessage($amount);
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
