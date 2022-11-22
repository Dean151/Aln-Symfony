<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Validator\ValidatorInterface;
use App\ApiPlatform\Dto\EmailInput;
use App\Email\NewPasswordEmailFactory;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[AsController]
final class ResetPassword extends AbstractNewPasswordController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly UserRepository $userRepository,
        ResetPasswordHelperInterface $resetPasswordHelper,
        MailerInterface $mailer,
        NewPasswordEmailFactory $emailFactory
    ) {
        parent::__construct($resetPasswordHelper, $mailer, $emailFactory);
    }

    public function __invoke(EmailInput $data): Response
    {
        $this->validator->validate($data);

        $user = $this->userRepository->findOneByEmail($data->email);
        if (null !== $user) {
            $this->sendNewPasswordEmail($user, 'reset_password');
        }

        return $this->json(['message' => 'Reset password instruction mail sent']);
    }
}
