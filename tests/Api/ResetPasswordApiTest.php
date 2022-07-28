<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\DataCollector\MessageDataCollector;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ResetPasswordApiTest extends ApiTestCase
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

    private function resetPasswordRequest(string $email): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('POST', 'user/reset', [
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

        $response = $client->request('POST', 'user/reset', [
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
}
