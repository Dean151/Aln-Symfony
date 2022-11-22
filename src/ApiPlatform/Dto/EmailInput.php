<?php

declare(strict_types=1);

namespace App\ApiPlatform\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\RegisterUser;
use App\Controller\ResetPassword;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/user/register',
            status: Response::HTTP_OK,
            controller: RegisterUser::class,
            openapiContext: [
                'tags' => ['User'],
                'summary' => 'Register using an email',
                'description' => 'Register using an email',
                'responses' => [
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
        ),
        new Post(
            uriTemplate: '/user/reset',
            status: Response::HTTP_OK,
            controller: ResetPassword::class,
            openapiContext: [
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
        ),
    ],
)]
final class EmailInput
{
    #[Assert\Email]
    public string $email;
}
