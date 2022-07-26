<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\ApiPlatform\Dto\EmailInput;
use App\Email\RegisterEmailFactory;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Uid\Uuid;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[AsController]
final class RegisterUserController extends AbstractController
{
    private ValidatorInterface $validator;
    private UserRepository $userRepository;
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private MailerInterface $mailer;
    private RegisterEmailFactory $registerEmailFactory;

    public function __construct(
        ValidatorInterface $validator,
        UserRepository $userRepository,
        ResetPasswordHelperInterface $resetPasswordHelper,
        MailerInterface $mailer,
        RegisterEmailFactory $registerEmailFactory
    ) {
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->mailer = $mailer;
        $this->registerEmailFactory = $registerEmailFactory;
    }

    public function __invoke(EmailInput $data): Response
    {
        $this->validator->validate($data);

        $user = $this->userRepository->findOneByEmail($data->email);
        if (null !== $user) {
            throw new ConflictHttpException('Email already in use');
        }

        $user = new User();
        $user->setIdentifier(Uuid::v4()->toRfc4122());
        $user->setEmail($data->email);
        $this->userRepository->add($user, true);

        $token = null;
        try {
            $token = $this->resetPasswordHelper->generateResetToken($user);

            $email = $this->registerEmailFactory->create($data->email, $token);
            $this->mailer->send($email);

            return $this->json(['message' => 'Register mail sent']);
        } catch (ResetPasswordExceptionInterface $e) {
            if ($token?->getToken()) {
                $this->resetPasswordHelper->removeResetRequest($token->getToken());
            }
            throw new HttpException(500, $e->getMessage(), $e);
        }
    }
}
