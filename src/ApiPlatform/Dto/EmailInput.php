<?php

declare(strict_types=1);

namespace App\ApiPlatform\Dto;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\RegisterUserController;
use App\Controller\ResetPasswordController;
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
                'summary' => 'Register using an email',
                'description' => 'Register using an email',
                'responses' => [
                    Response::HTTP_OK => [
                        'description' => 'Register email has been sent.',
                    ],
                    Response::HTTP_CONFLICT => [
                        'description' => 'Email address already in use.',
                    ],
                ],
            ],
        ],
        'reset' => [
            'method' => 'POST',
            'path' => '/user/reset',
            'controller' => ResetPasswordController::class,
            'status' => Response::HTTP_OK,
            'openapi_context' => [
                'tags' => ['User'],
                'summary' => 'Request password reset for an account',
                'description' => 'Request password reset for an account',
                'responses' => [
                    Response::HTTP_OK => [
                        'description' => 'Reset password instruction email has been sent.',
                    ],
                    Response::HTTP_NOT_FOUND => [
                        'description' => 'Email not found.',
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
