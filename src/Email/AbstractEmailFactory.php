<?php

declare(strict_types=1);

namespace App\Email;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

use function Safe\preg_replace;

abstract class AbstractEmailFactory
{
    private readonly string $unsubscribeUrl;

    public function __construct(
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator,
        #[Autowire('%env(string:EMAIL_SENDER)%')]
        private readonly string $senderEmail,
        #[Autowire('%env(string:EMAIL_UNSUBSCRIBE)%')]
        private readonly string $unsubscribeEmail,
        #[Autowire('%env(string:SITE_NAME)%')]
        protected readonly string $siteName,
        #[Autowire('%env(string:SITE_BASE_URL)%')]
        protected readonly string $siteBaseUrl,
        #[Autowire('%env(string:API_BASE_URL)%')]
        string $apiBaseUrl,
    ) {
        $this->unsubscribeUrl = $apiBaseUrl.'/email/unsubscribe';
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

        $this->setUnsubscribeHeaders($email, $recipient);

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

    private function setUnsubscribeHeaders(Email $email, User $recipient): void
    {
        $recipientEmail = $recipient->getEmail();
        $unsubscribeToken = $recipient->getUnsubscribeToken();
        $unsubscribeMailto = "mailto:{$this->unsubscribeEmail}?subject=unsubscribe&body={$unsubscribeToken}";
        $unsubscribeUrl = "{$this->unsubscribeUrl}?email={$recipientEmail}&token={$unsubscribeToken}";
        $unsubscribeBody = sprintf('<%s>, <%s>', $unsubscribeMailto, $unsubscribeUrl);
        $email->getHeaders()->addTextHeader('List-Unsubscribe', $unsubscribeBody);
        $email->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
    }

    protected function getLocale(): string
    {
        $components = explode('_', $this->translator->getLocale());

        return reset($components);
    }

    /**
     * @param array<string, string> $context
     */
    protected function translate(string $id, array $context, string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($id, $context, $domain, $locale);
    }
}
