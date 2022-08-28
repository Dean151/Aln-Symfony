<?php

declare(strict_types=1);

namespace App\Controller;

use App\Email\NewPasswordEmailFactory;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

abstract class AbstractNewPasswordController extends AbstractController
{
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private MailerInterface $mailer;
    private NewPasswordEmailFactory $emailFactory;

    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper, MailerInterface $mailer, NewPasswordEmailFactory $emailFactory)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->mailer = $mailer;
        $this->emailFactory = $emailFactory;
    }

    protected function sendNewPasswordEmail(User $user, string $type): void
    {
        $token = null;
        try {
            $token = $this->resetPasswordHelper->generateResetToken($user);
            $email = $this->emailFactory->create($type, $user, $token);
            $this->mailer->send($email);
        } catch (TooManyPasswordRequestsException $e) {
            throw new HttpException(Response::HTTP_NOT_ACCEPTABLE, $e->getReason());
        } catch (ResetPasswordExceptionInterface $e) {
            if ($token?->getToken()) {
                $this->resetPasswordHelper->removeResetRequest($token->getToken());
            }
            throw new HttpException(500, $e->getReason(), $e);
        }
    }
}
