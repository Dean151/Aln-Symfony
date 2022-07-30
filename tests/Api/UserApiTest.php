<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Zenstruck\Foundry\Test\Factories;

final class UserApiTest extends AuthenticatedApiTestCase
{
    use Factories;

    public function testAuthentication(): void
    {
        $response = $this->authenticateRequest('user.feeder@example.com', 'password');
        $this->assertResponseIsSuccessful();
        $json = $response->toArray();
        $this->assertArrayHasKey('token', $json);
        $this->assertIsString($json['token']);
    }

    /**
     * @dataProvider provideWrongCredentials
     */
    public function testAuthenticationWrongCredentials(string $email, string $password, int $expectedStatusCode): void
    {
        $this->authenticateRequest($email, $password);
        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public function provideWrongCredentials(): \Generator
    {
        yield ['', '', Response::HTTP_UNAUTHORIZED];
        yield ['', 'password', Response::HTTP_UNAUTHORIZED];
        yield ['unkwown_email@example.com', '', Response::HTTP_UNAUTHORIZED];
        yield ['not_an_email', 'password', Response::HTTP_UNAUTHORIZED];
        yield ['unkwown_email@example.com', 'password', Response::HTTP_UNAUTHORIZED];
    }

    public function testCurrentUserFetch(): void
    {
        $user = $this->getUserByEmail('user.nofeeder@example.com');
        $this->fetchCurrentUserRequest($user);
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'feeders' => [],
        ]);
    }

    public function testCurrentUserUnauthenticatedFetch(): void
    {
        $this->fetchCurrentUserRequest();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUpdatePassword(): void
    {
        $newPassword = UserFactory::faker()->password();
        $user = $this->getUserByEmail('user.nofeeder@example.com');
        $oldHash = $user->getPassword();
        $userId = $user->getId();
        $this->assertNotNull($userId);
        $this->updateUserRequest($userId, ['password' => $newPassword], $user);
        $this->assertResponseIsSuccessful();
        $user = $this->getUserByEmail('user.nofeeder@example.com');
        $this->assertNotEquals($oldHash, $user->getPassword());

        // Test that password is correct
        $isValid = $this->getPasswordHasher()->isPasswordValid($user, $newPassword);
        $this->assertTrue($isValid);
    }

    private function authenticateRequest(string $email, string $password): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('POST', '/user/login', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);
    }

    private function fetchCurrentUserRequest(?UserInterface $authenticatedAs = null): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('GET', '/user/me', [
            'headers' => [
                'Accept' => 'application/json',
            ] + $this->getHeadersIfAuthenticated($authenticatedAs),
        ]);
    }

    /**
     * @param array<string, string> $json
     */
    private function updateUserRequest(int $userId, array $json, ?UserInterface $authenticatedAs = null): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('PUT', "/user/{$userId}", [
            'headers' => [
                'Accept' => 'application/json',
            ] + $this->getHeadersIfAuthenticated($authenticatedAs),
            'json' => $json,
        ]);
    }

    private function getPasswordHasher(): UserPasswordHasher
    {
        $hasher = self::getContainer()->get(UserPasswordHasher::class);
        \assert($hasher instanceof UserPasswordHasher);

        return $hasher;
    }
}
