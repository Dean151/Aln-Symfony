<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\DataCollector\MessageDataCollector;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class RegisterApiTest extends ApiTestCase
{
    public function testRegisterWithEmail(): void
    {
        $this->registerEmailRequest('new_user@example.com', $mailer);
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals(['message' => 'Register mail sent']);

        $this->assertInstanceOf(MessageDataCollector::class, $mailer);
        $events = $mailer->getEvents()->getEvents();
        $this->assertCount(1, $events);

        $event = $events[0];
        $this->assertEquals('no-reply@example.com', $event->getEnvelope()->getSender()->getAddress());
        $recipients = $event->getEnvelope()->getRecipients();
        $this->assertCount(1, $recipients);
        $this->assertEquals('new_user@example.com', $recipients[0]->getAddress());
    }

    public function testRegisterWithInvalidEmail(): void
    {
        $this->registerEmailRequest('not_an_email');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @depends testRegisterWithEmail
     */
    public function testRegisterWithUsedEmail(): void
    {
        $this->registerEmailRequest('new_user@example.com');
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    private function registerEmailRequest(string $email, ?MessageDataCollector &$mailer = null): ResponseInterface
    {
        $client = self::createClient();
        $client->enableProfiler();

        $response = $client->request('POST', 'user/register', [
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
