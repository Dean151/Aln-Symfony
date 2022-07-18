<?php

namespace App\Controller;

use App\Aln\Socket\MessageEnqueueInterface;
use App\Aln\Socket\Messages\ChangePlanningMessage;
use App\Entity\AlnFeeder;
use App\Entity\AlnMeal;
use App\Entity\AlnPlanning;
use App\Repository\AlnMealRepository;
use App\Repository\AlnPlanningRepository;
use Doctrine\Persistence\ManagerRegistry;

use function PHPUnit\Framework\assertNotNull;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ChangePlanningController extends AbstractSocketController
{
    private AlnPlanningRepository $planningRepository;
    private AlnMealRepository $mealRepository;
    private ManagerRegistry $doctrine;

    public function __construct(
        MessageEnqueueInterface $queue,
        AlnPlanningRepository $planningRepository,
        AlnMealRepository $mealRepository,
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
        assertNotNull($planning);
        $feeder = $data;

        $message = new ChangePlanningMessage($planning->meals);
        $this->sendSocketMessage($feeder, $message);

        $alnPlanning = new AlnPlanning();
        $feeder->addPlanning($alnPlanning);
        $this->planningRepository->add($alnPlanning);
        foreach ($planning->meals as $meal) {
            $alnMeal = new AlnMeal();
            $alnMeal->setTimeFromInput($meal->time);
            $alnMeal->setAmount($meal->amount);
            $alnMeal->setIsEnabled($meal->isEnabled);
            $feeder->addMeal($alnMeal);
            $alnPlanning->addMeal($alnMeal);
            $this->mealRepository->add($alnMeal);
        }
        $this->doctrine->getManager()->flush();

        return $this->json([
            'message' => 'Planning have been changed',
        ]);
    }
}
