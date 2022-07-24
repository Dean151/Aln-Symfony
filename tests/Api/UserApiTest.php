<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class UserApiTest extends AuthenticatedApiTestCase
{
    public function testAuthentication(): void
    {
        $this->authenticateRequest('user.feeder@example.com', 'password');
        $this->assertResponseIsSuccessful();
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

    private function authenticateRequest(string $email, string $password): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('POST', 'user/login', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);
    }
}
