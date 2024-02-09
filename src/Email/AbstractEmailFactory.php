<?php

declare(strict_types=1);

namespace App\Email;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Message;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

use function Safe\preg_replace;

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

        $body = $this->twig->render($template, $context);
        $text = strip_tags(preg_replace('{<(head|style)\b.*?</\1>}is', '', $body));
        $email = $email->html($this->buildHtml($subject, $body))->text($text);

        return new Message($email->getPreparedHeaders(), $email->getBody());
    }

    private function buildHtml(string $subject, string $body): string
    {
        return $this->twig->render('emails/base_mail.html.twig', [
            'language' => $this->getLocale(),
            'subject' => $subject,
            'body' => $body,
        ]);
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
