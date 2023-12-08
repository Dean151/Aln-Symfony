<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Validator\ValidatorInterface;
use App\ApiResource\Dto\EmailInput;
use App\Email\NewPasswordEmailFactory;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Uid\Uuid;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[AsController]
final class RegisterUser extends AbstractNewPasswordController
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
        } else {
            $user = new User();
            $user->setIdentifier(Uuid::v4()->toRfc4122());
            $user->setEmail($data->email);
            $this->userRepository->add($user, true);

            $this->sendNewPasswordEmail($user, 'register');
        }

        return $this->json(['message' => 'Register mail sent']);
    }
}
