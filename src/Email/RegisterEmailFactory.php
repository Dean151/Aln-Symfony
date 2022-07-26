<?php

declare(strict_types=1);

namespace App\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

final class RegisterEmailFactory
{
    private TranslatorInterface $translator;
    private string $senderEmail;
    private string $siteName;
    private string $siteBaseUrl;

    public function __construct(ContainerBagInterface $params, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->senderEmail = $params->get('email.sender');
        $this->siteBaseUrl = $params->get('site.base_url');
        $this->siteName = $params->get('site.name');
    }

    public function create(string $recipientAddress, ResetPasswordToken $token): Email
    {
        $locale = 'en';

        $email = new TemplatedEmail();
        $email->from($this->senderEmail);
        $email->to($recipientAddress);

        $email->subject($this->translator->trans('register.subject', ['%site_name%' => $this->siteName]));
        $email->htmlTemplate(sprintf('emails/register_%s.html.twig', $locale));
        $email->context([
            'url' => $this->buildUrl($token, $locale),
            'recipient_email' => $recipientAddress,
            'site_name' => 'Cat Feeder Initiative',
        ]);

        return $email;
    }

    private function buildUrl(ResetPasswordToken $token, string $locale): string
    {
        return "{$this->siteBaseUrl}/{$locale}/register/{$token->getToken()}";
    }
}
