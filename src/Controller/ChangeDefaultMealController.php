<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Aln\Socket\MessageEnqueueInterface;
use App\Aln\Socket\Messages\ChangeDefaultMealMessage;
use App\Entity\AlnFeeder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ChangeDefaultMealController extends AbstractSocketController
{
    private ValidatorInterface $validator;
    private ManagerRegistry $doctrine;

    public function __construct(
        MessageEnqueueInterface $queue,
        ValidatorInterface $validator,
        ManagerRegistry $doctrine
    ) {
        $this->validator = $validator;
        $this->doctrine = $doctrine;
        parent::__construct($queue);
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
