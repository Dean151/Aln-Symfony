<?php

declare(strict_types=1);

namespace App\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// TODO: move to ProcessorInterface
final class UserDataPersister implements DataPersisterInterface
{
    private UserRepository $repository;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserRepository $repository, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->repository = $repository;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function supports($data): bool
    {
        return $data instanceof User;
    }

    /**
     * @param User $data
     */
    public function persist($data): User
    {
        if ($data->getPlainPassword()) {
            $hash = $this->userPasswordHasher->hashPassword($data, $data->getPlainPassword());
            $data->setPassword($hash);
            $data->eraseCredentials();
        }
        $this->repository->add($data, true);

        return $data;
    }

    public function remove($data): void
    {
        $this->repository->remove($data);
    }
}
