<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Repository\ResetPasswordRequestRepository;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\DataCollector\MessageDataCollector;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class RegisterApiTest extends AuthenticatedApiTestCase
{
    public function testRegisterWithEmail(): void
    {
        $this->registerEmailRequestWithProfiler('new_user@example.com', $mailer);
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

        $user = $this->getUserByEmail('new_user@example.com');
        $this->getResetPasswordRequestRepository()->deleteAllFor($user);
    }

    public function testRegisterWithInvalidEmail(): void
    {
        $this->registerEmailRequest('not_an_email');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Depends('testRegisterWithEmail')]
    public function testRegisterWithUsedEmail(): void
    {
        $this->registerEmailRequestWithProfiler('user.nofeeder@example.com', $mailer);
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals(['message' => 'Register mail sent']);

        $this->assertInstanceOf(MessageDataCollector::class, $mailer);
        $events = $mailer->getEvents()->getEvents();
        $this->assertCount(1, $events); // A password reset mail should be sent

        $user = $this->getUserByEmail('user.nofeeder@example.com');
        $this->getResetPasswordRequestRepository()->deleteAllFor($user);
    }

    private function registerEmailRequest(string $email): ResponseInterface
    {
        $client = self::createClient();

        return $client->request('POST', '/user/register', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'email' => $email,
            ],
        ]);
    }

    private function registerEmailRequestWithProfiler(string $email, ?MessageDataCollector &$mailer): ResponseInterface
    {
        $client = self::createClient();
        $client->enableProfiler();

        $response = $client->request('POST', '/user/register', [
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

    private function getResetPasswordRequestRepository(): ResetPasswordRequestRepository
    {
        return self::getContainer()->get(ResetPasswordRequestRepository::class);
    }
}
