<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AlnFeeder;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class AssociateFeeder extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    public function __invoke(AlnFeeder $data): Response
    {
        $feeder = $data;

        if (null !== $feeder->getOwner()) {
            throw $this->createAccessDeniedException('Feeder already associated');
        }

        // Cross-check IP
        $current_ip = $this->getRequest()->getClientIp();
        if (null === $feeder->getIp() || null === $current_ip || $feeder->getIp() !== $current_ip) {
            throw $this->createAccessDeniedException("Your current IP ($current_ip) does not match the feeder IP. Are you connected to the same network?");
        }

        $user = $this->getUser();
        assert($user instanceof User);
        $feeder->setOwner($user);

        $this->doctrine->getManager()->flush();

        return $this->json([
            'message' => 'Feeder associated',
        ]);
    }

    private function getRequest(): Request
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        assert($request instanceof Request);

        return $request;
    }
}
