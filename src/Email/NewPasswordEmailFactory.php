<?php

declare(strict_types=1);

namespace App\Email;

use App\Entity\User;
use Symfony\Component\Mime\Message;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

final class NewPasswordEmailFactory extends AbstractEmailFactory
{
    public function create(string $type, User $recipient, ResetPasswordToken $token): Message
    {
        if (!in_array($type, ['register', 'reset_password'])) {
            throw new \InvalidArgumentException('$type is invalid');
        }

        $subject = $this->translate(sprintf('%s.subject', $type), ['%site_name%' => $this->siteName]);
        $template = sprintf('emails/%s_%s.html.twig', $type, $this->getLocale());
        $context = [
            'url' => $this->buildUrl($token),
            'recipient_email' => $recipient->getEmail(),
            'site_name' => $this->siteName,
        ];

        return $this->createTemplatedEmail($recipient, $subject, $template, $context);
    }

    private function buildUrl(ResetPasswordToken $token): string
    {
        return "{$this->siteBaseUrl}/reset/{$token->getToken()}";
    }
}
