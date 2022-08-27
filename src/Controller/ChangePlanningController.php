<?php

declare(strict_types=1);

namespace App\Controller;

use App\ApiPlatform\Dto\PlanningInput;
use App\Entity\AlnFeeder;
use App\Entity\AlnPlannedMeal;
use App\Entity\AlnPlanning;
use App\Queue\MessageEnqueueInterface;
use App\Repository\AlnPlannedMealRepository;
use App\Repository\AlnPlanningRepository;
use App\Socket\Messages\ChangePlanningMessage;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ChangePlanningController extends AbstractSocketController
{
    private AlnPlanningRepository $planningRepository;
    private AlnPlannedMealRepository $mealRepository;
    private ManagerRegistry $doctrine;

    public function __construct(
        MessageEnqueueInterface $queue,
        AlnPlanningRepository $planningRepository,
        AlnPlannedMealRepository $mealRepository,
        ManagerRegistry $doctrine
    ) {
        $this->doctrine = $doctrine;
        $this->planningRepository = $planningRepository;
        $this->mealRepository = $mealRepository;
        parent::__construct($queue);
    }

    public function __invoke(AlnFeeder $data): Response
    {
        // Validation is made by DTO

        $planning = $data->planning;
        assert($planning instanceof PlanningInput);
        $feeder = $data;

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
