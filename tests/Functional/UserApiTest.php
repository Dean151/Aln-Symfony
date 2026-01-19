<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Zenstruck\Foundry\Test\Factories;

use function Zenstruck\Foundry\faker;

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

    #[DataProvider('provideWrongCredentials')]
    public function testAuthenticationWrongCredentials(string $email, string $password, int $expectedStatusCode): void
    {
        $this->authenticateRequest($email, $password);
        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public static function provideWrongCredentials(): \Generator
    {
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
        $newPassword = faker()->password();
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

        return $client->request('POST', '/user/login', $this->getOptions()->setJson([
            'email' => $email,
            'password' => $password,
        ])->toArray());
    }

    private function fetchCurrentUserRequest(?UserInterface $authenticatedAs = null): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('GET', '/user/me', $this->getOptions($authenticatedAs)->toArray());
    }

    /**
     * @param array<string, string> $json
     */
    private function updateUserRequest(int $userId, array $json, ?UserInterface $authenticatedAs = null): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('PATCH', "/user/{$userId}", $this->getOptions($authenticatedAs, 'application/merge-patch+json')->setJson($json)->toArray());
    }

    private function getPasswordHasher(): UserPasswordHasher
    {
        return self::getContainer()->get(UserPasswordHasher::class);
    }
}
