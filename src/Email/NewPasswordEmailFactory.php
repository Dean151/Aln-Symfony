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
        $template = sprintf('emails/%s_%s.txt.twig', $type, $this->getLocale());
        $context = [
            'token' => $token->getToken(),
            'expires_at' => $token->getExpiresAt()->format('Y-m-d H:i T'),
            'api_url' => rtrim($this->siteBaseUrl, '/'),
            'site_name' => $this->siteName,
        ];

        return $this->createTemplatedEmail($recipient, $subject, $template, $context);
    }
}
