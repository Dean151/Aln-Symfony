<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AlnFeeder;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class AssociateFeederController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function __invoke(AlnFeeder $data): Response
    {
        $feeder = $data;

        if (null !== $feeder->getOwner()) {
            throw $this->createAccessDeniedException('Feeder already associated');
        }

        $user = $this->getUser();
        assert($user instanceof User);
        $feeder->setOwner($user);

        $this->doctrine->getManager()->flush();

        return $this->json([
            'message' => 'Feeder associated',
        ]);
    }
}
