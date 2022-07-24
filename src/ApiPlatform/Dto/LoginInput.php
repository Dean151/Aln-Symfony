<?php

declare(strict_types=1);

namespace App\ApiPlatform\Dto;

final class LoginInput
{
    public string $email;

    public string $password;
}
