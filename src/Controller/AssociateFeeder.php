<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Validator\ValidatorInterface;
use App\ApiPlatform\Dto\IdentifierInput;
use App\Entity\User;
use App\Repository\AlnFeederRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class AssociateFeeder extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly AlnFeederRepository $repository,
    ) {
    }

    public function __invoke(IdentifierInput $data): Response
    {
        $this->validator->validate($data);

        $feeder = $this->repository->findOneByIdentifier($data->identifier);
        if (null === $feeder) {
            throw $this->createNotFoundException();
        }

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

        $this->repository->add($feeder, true);

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
