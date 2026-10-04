<?php

declare(strict_types=1);

namespace App\Email;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Message;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class AbstractEmailFactory
{
    public function __construct(
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator,
        #[Autowire('%env(string:EMAIL_SENDER)%')]
        private readonly string $senderEmail,
        #[Autowire('%env(string:SITE_NAME)%')]
        protected readonly string $siteName,
        #[Autowire('%env(string:SITE_BASE_URL)%')]
        protected readonly string $siteBaseUrl,
    ) {
    }

    /**
     * @param array<string, string> $context
     */
    protected function createTemplatedEmail(User $recipient, string $subject, string $template, array $context): Message
    {
        $email = new TemplatedEmail();
        $email = $email->to($recipient->getEmail())
            ->from($this->senderEmail)
            ->subject($subject);

        $email = $email->text($this->twig->render($template, $context));

        return new Message($email->getPreparedHeaders(), $email->getBody());
    }

    protected function getLocale(): string
    {
        $components = explode('_', $this->translator->getLocale());
        $langcode = reset($components);

        // FIXME: find a better way to restrict to "supported languages"
        return in_array($langcode, ['en']) ? $langcode : 'en';
    }

    /**
     * @param array<string, string> $context
     */
    protected function translate(string $id, array $context, ?string $domain = null, ?string $locale = null): string
    {
        return $this->translator->trans($id, $context, $domain, $locale ?? $this->getLocale());
    }
}
