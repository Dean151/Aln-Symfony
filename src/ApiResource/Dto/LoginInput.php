<?php

declare(strict_types=1);

namespace App\ApiResource\Dto;

use Symfony\Component\Serializer\Attribute\Groups;

final class LoginInput
{
    #[Groups(['user:input'])]
    public string $email;

    #[Groups(['user:input'])]
    public string $password;
}
