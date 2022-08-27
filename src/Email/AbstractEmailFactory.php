<?php

declare(strict_types=1);

namespace App\Email;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractEmailFactory
{
    private TranslatorInterface $translator;
    private string $senderEmail;
    private string $unsubscribeEmail;
    private string $unsubscribeUrl;
    private string $dkimKey;
    private string $dkimPassphrase;

    protected string $siteName;
    protected string $siteBaseUrl;

    public function __construct(ContainerBagInterface $params, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->senderEmail = $params->get('email.sender');
        $this->unsubscribeEmail = $params->get('email.unsubscribe');
        $this->unsubscribeUrl = $params->get('api.base_url');
        $this->dkimKey = $params->get('email.dkim.key');
        $this->dkimPassphrase = $params->get('email.dkim.passphrase');
        $this->siteBaseUrl = $params->get('site.base_url');
        $this->siteName = $params->get('site.name');
    }

    /**
     * @param array<string, string> $context
     */
    protected function createTemplatedEmail(User $recipient, string $subject, string $template, array $context): Message
    {
        $email = (new TemplatedEmail())
            ->to($recipient->getEmail())
            ->from($this->senderEmail)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        $this->setUnsubscribeHeaders($email, $recipient);

        return $this->signEmailWithDkim($email);
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

    private function signEmailWithDkim(Email $email): Message
    {
        print_r($this->dkimKey);
        if (empty($this->dkimKey)) {
            return $email;
        }

        $domain = explode('@', $this->senderEmail)[1];
        $signer = new DkimSigner($this->dkimKey, $domain, 'sf', [], $this->dkimPassphrase);

        return $signer->sign($email);
    }

    protected function getLocale(): string
    {
        return $this->translator->getLocale();
    }

    /**
     * @param array<string, string> $context
     */
    protected function translate(string $id, array $context, string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($id, $context, $domain, $locale);
    }
}
