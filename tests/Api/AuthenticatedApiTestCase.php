<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
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

    /**
     * @return string[]
     */
    protected function getHeadersIfAuthenticated(?UserInterface $user): array
    {
        return $user ? $this->getAuthenticatedHeaders($user) : [];
    }

    private function getUserRepository(): UserRepository
    {
        $repository = self::getContainer()->get(UserRepository::class);
        $this->assertInstanceOf(UserRepository::class, $repository);

        return $repository;
    }

    /**
     * @return string[]
     */
    private function getAuthenticatedHeaders(UserInterface $user): array
    {
        return [
            'Authorization' => $this->getAuthorizationHeader($user),
        ];
    }

    private function getAuthorizationHeader(UserInterface $user): string
    {
        return 'Bearer '.$this->getAuthenticationToken($user);
    }

    private function getAuthenticationToken(UserInterface $user): string
    {
        return $this->getJwtManager()->create($user);
    }

    private function getJwtManager(): JWTManager
    {
        $manager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $this->assertInstanceOf(JWTManager::class, $manager);

        return $manager;
    }
}
