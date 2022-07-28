<?php

declare(strict_types=1);

namespace App\Security\Authenticator\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class InvalidResetPasswordTokenException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Invalid reset password token';
    }
}
