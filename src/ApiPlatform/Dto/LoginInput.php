<?php

declare(strict_types=1);

namespace App\ApiPlatform\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final class LoginInput
{
    #[Groups(['user:input'])]
    public string $email;

    #[Groups(['user:input'])]
    public string $password;
}
