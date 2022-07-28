<?php

declare(strict_types=1);

namespace App\ApiPlatform\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class IdentifierInput
{
    #[Assert\NotBlank]
    public string $identifier;
}
