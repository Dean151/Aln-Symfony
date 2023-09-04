<?php

declare(strict_types=1);

namespace App\ApiPlatform\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<User>
 */
final class UserProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    /**
     * @param User                 $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        if ($data->getPlainPassword()) {
            $hash = $this->userPasswordHasher->hashPassword($data, $data->getPlainPassword());
            $data->setPassword($hash);
            $data->eraseCredentials();
        }
        $this->repository->add($data, true);

        return $data;
    }
}
