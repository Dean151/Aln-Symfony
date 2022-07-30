<?php

declare(strict_types=1);

namespace App\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserDataPersister implements DataPersisterInterface
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->entityManager = $entityManager;
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
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }

    public function remove($data): void
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}
