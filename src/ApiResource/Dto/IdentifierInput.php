<?php

declare(strict_types=1);

namespace App\ApiResource\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class IdentifierInput
{
    #[Assert\NotBlank]
    public string $identifier;
}
