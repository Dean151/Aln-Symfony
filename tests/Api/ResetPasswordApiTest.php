<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Repository\ResetPasswordRequestRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\DataCollector\MessageDataCollector;
use Symfony\Contracts\HttpClient\ResponseInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

final class ResetPasswordApiTest extends AuthenticatedApiTestCase
{
    public function testResetPasswordWithEmail(): void
    {
        $this->resetPasswordRequestWithProfiler('user.feeder@example.com', $mailer);
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals(['message' => 'Reset password instruction mail sent']);

        $this->assertInstanceOf(MessageDataCollector::class, $mailer);
        $events = $mailer->getEvents()->getEvents();
        $this->assertCount(1, $events);

        $event = $events[0];
        $this->assertEquals('no-reply@example.com', $event->getEnvelope()->getSender()->getAddress());
        $recipients = $event->getEnvelope()->getRecipients();
        $this->assertCount(1, $recipients);
        $this->assertEquals('user.feeder@example.com', $recipients[0]->getAddress());
    }

    /**
     * @depends testResetPasswordWithEmail
     */
    public function testResetPasswordAlreadyRequested(): void
    {
        $this->resetPasswordRequest('user.feeder@example.com');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_ACCEPTABLE);

        $user = $this->getUserByEmail('user.feeder@example.com');
        $this->getResetPasswordRequestRepository()->deleteAllFor($user);
    }

    public function testResetPasswordWithInvalidEmail(): void
    {
        $this->resetPasswordRequest('not_an_email');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testResetPasswordWithUnusedEmail(): void
    {
        $this->resetPasswordRequestWithProfiler('unused_email@example.com', $mailer);
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals(['message' => 'Reset password instruction mail sent']);

        $this->assertInstanceOf(MessageDataCollector::class, $mailer);
        $events = $mailer->getEvents()->getEvents();
        $this->assertCount(0, $events);
    }

    public function testResetPasswordTokenConsume(): void
    {
        $user = $this->getUserByEmail('user.nofeeder@example.com');
        $token = $this->getResetPasswordHelper()->generateResetToken($user)->getToken();
        $response = $this->resetPasswordConsumeTokenRequest($token);
        $this->assertResponseIsSuccessful();
        $json = $response->toArray();
        $this->assertArrayHasKey('token', $json);
        $this->assertIsString($json['token']);

        // Try to reuse a one-time use token
        $this->resetPasswordConsumeTokenRequest($token);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testNonValidResetPasswordToken(): void
    {
        $this->resetPasswordConsumeTokenRequest('not_a_valid_token');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function resetPasswordRequest(string $email): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('POST', '/user/reset', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'email' => $email,
            ],
        ]);
    }

    private function resetPasswordRequestWithProfiler(string $email, ?MessageDataCollector &$mailer): ResponseInterface
    {
        $client = self::createClient();
        $client->enableProfiler();

        $response = $client->request('POST', '/user/reset', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'email' => $email,
            ],
        ]);

        if ($profile = $client->getProfile()) {
            $collector = $profile->getCollector('mailer');
            $this->assertInstanceOf(MessageDataCollector::class, $collector);
            $mailer = $collector;
        }

        return $response;
    }

    private function resetPasswordConsumeTokenRequest(string $token): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('POST', '/user/reset/consume', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'token' => $token,
            ],
        ]);
    }

    private function getResetPasswordHelper(): ResetPasswordHelperInterface
    {
        $helper = self::getContainer()->get(ResetPasswordHelperInterface::class);
        $this->assertInstanceOf(ResetPasswordHelperInterface::class, $helper);

        return $helper;
    }

    private function getResetPasswordRequestRepository(): ResetPasswordRequestRepository
    {
        $repository = self::getContainer()->get(ResetPasswordRequestRepository::class);
        $this->assertInstanceOf(ResetPasswordRequestRepository::class, $repository);

        return $repository;
    }
}
