<?php

declare(strict_types=1);

namespace App\Security\Authenticator;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;

use function Safe\json_decode;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

final class ResetPasswordTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly AuthenticationSuccessHandler $authenticationSuccessHandler,
        private readonly AuthenticationFailureHandler $authenticationFailureHandler,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        if (!$request->isMethod('POST')) {
            return false;
        }

        return '/user/reset/consume' === $request->getPathInfo() && null !== $this->getToken($request);
    }

    public function authenticate(Request $request): Passport
    {
        try {
            $token = $this->getToken($request);
            if (!$token) {
                throw new BadRequestException('Missing token.');
            }
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
            $this->resetPasswordHelper->removeResetRequest($token);
            \assert($user instanceof User);

            return new SelfValidatingPassport(new UserBadge($user->getEmail()));
        } catch (ResetPasswordExceptionInterface $e) {
            throw new Exception\InvalidResetPasswordTokenException(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, 0, $e);
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->authenticationSuccessHandler->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->authenticationFailureHandler->onAuthenticationFailure($request, $exception);
    }

    private function getToken(Request $request): ?string
    {
        $content = json_decode($request->getContent());

        return $content->token ?? null;
    }
}
