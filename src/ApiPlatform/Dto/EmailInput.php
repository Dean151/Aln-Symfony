<?php

declare(strict_types=1);

namespace App\ApiPlatform\Dto;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\RegisterUserController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    collectionOperations: [
        'register' => [
            'method' => 'POST',
            'path' => '/user/register',
            'controller' => RegisterUserController::class,
            'status' => Response::HTTP_OK,
            'openapi_context' => [
                'tags' => ['User'],
                'summary' => 'Register with an email',
                'description' => 'Register with an email',
                'responses' => [
                    Response::HTTP_OK => [
                        'description' => 'Register email has been sent.',
                    ],
                    Response::HTTP_CONFLICT => [
                        'description' => 'Email address already in use',
                    ],
                ],
            ],
        ],
    ],
    itemOperations: [],
)]
final class EmailInput
{
    #[Assert\Email]
    public string $email;
}
