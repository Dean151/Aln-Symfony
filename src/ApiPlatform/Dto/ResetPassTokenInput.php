<?php

declare(strict_types=1);

namespace App\ApiPlatform\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final class ResetPassTokenInput
{
    #[Groups(['user:input'])]
    public string $token;
}
