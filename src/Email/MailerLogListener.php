<?php

declare(strict_types=1);

namespace App\Email;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Event\FailedMessageEvent;
use Symfony\Component\Mailer\Event\SentMessageEvent;

use function Safe\preg_replace;

final class MailerLogListener
{
    public function __construct(
        #[Target('mailer')]
        private readonly LoggerInterface $logger,
    ) {
    }

    #[AsEventListener]
    public function onSent(SentMessageEvent $event): void
    {
        $message = $event->getMessage();
        $this->logger->info('Email sent', [
            'message_id' => $message->getMessageId(),
            'from' => $message->getEnvelope()->getSender()->toString(),
            'to' => array_map(static fn ($address) => $address->toString(), $message->getEnvelope()->getRecipients()),
            'smtp' => $this->redactCredentials($message->getDebug()),
        ]);
    }

    private function redactCredentials(string $debug): string
    {
        // Client lines answering a "334" auth challenge, and inline "AUTH <mechanism> <payload>"
        return preg_replace(
            ['/(< 334[^\n]*\n\[[^\]]*\] > )[^\n]*/', '/(> AUTH \S+ )\S+/'],
            '$1[redacted]',
            $debug,
        );
    }

    #[AsEventListener]
    public function onFailed(FailedMessageEvent $event): void
    {
        $this->logger->error('Email failed', [
            'exception' => $event->getError(),
        ]);
    }
}
