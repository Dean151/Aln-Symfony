<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AuthenticatedApiTestCase extends ApiTestCase
{
    protected function getUserByEmail(string $email): User
    {
        $repository = $this->getUserRepository();
        $user = $repository->findOneByEmail($email);
        $this->assertNotNull($user);

        return $user;
    }

    protected function getOptions(?UserInterface $authenticatedAs = null): HttpOptions
    {
        $options = new HttpOptions();
        $options->setHeaders(['Accept' => 'application/json']);
        if (null !== $authenticatedAs) {
            $options->setAuthBearer($this->getAuthenticationToken($authenticatedAs));
        }

        return $options;
    }

    private function getUserRepository(): UserRepository
    {
        return self::getContainer()->get(UserRepository::class);
    }

    private function getAuthenticationToken(UserInterface $user): string
    {
        return $this->getJwtManager()->create($user);
    }

    private function getJwtManager(): JWTManager
    {
        return self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
    }
}
