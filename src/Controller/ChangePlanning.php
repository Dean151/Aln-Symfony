<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Validator\ValidatorInterface;
use App\ApiResource\Dto\PlanningInput;
use App\Entity\AlnPlannedMeal;
use App\Entity\AlnPlanning;
use App\Queue\MessageEnqueueInterface;
use App\Repository\AlnPlannedMealRepository;
use App\Repository\AlnPlanningRepository;
use App\Socket\Messages\ChangePlanningMessage;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ChangePlanning extends AbstractSocketController
{
    public function __construct(
        #[Autowire('%env(float:FEEDER_RESPONSE_TIMEOUT)%')]
        float $timeout,
        MessageEnqueueInterface $queue,
        private readonly ValidatorInterface $validator,
        private readonly AlnPlanningRepository $planningRepository,
        private readonly AlnPlannedMealRepository $mealRepository,
        private readonly ManagerRegistry $doctrine,
    ) {
        parent::__construct($timeout, $queue);
    }

    public function __invoke(PlanningInput $data): Response
    {
        $this->validator->validate($data);

        $planning = $data;
        $feeder = $data->feeder;

        $message = new ChangePlanningMessage($planning->meals);
        $this->sendSocketMessage($feeder, $message);

        $alnPlanning = new AlnPlanning();
        $feeder->addPlanning($alnPlanning);
        foreach ($planning->meals as $meal) {
            $alnMeal = new AlnPlannedMeal();
            $alnMeal->setTime($meal->time->toArray());
            $alnMeal->setAmount($meal->amount);
            $alnMeal->setIsEnabled($meal->isEnabled);
            $alnPlanning->addMeal($alnMeal);
            $this->mealRepository->add($alnMeal);
        }
        $this->planningRepository->add($alnPlanning);
        $this->doctrine->getManager()->flush();

        return $this->json([
            'message' => 'Planning have been changed',
        ]);
    }
}
