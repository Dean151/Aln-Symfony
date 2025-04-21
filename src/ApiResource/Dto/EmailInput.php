<?php

declare(strict_types=1);

namespace App\ApiResource\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use App\Controller\RegisterUser;
use App\Controller\ResetPassword;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/user/register',
            status: HttpResponse::HTTP_OK,
            controller: RegisterUser::class,
            openapi: new Operation(
                tags: ['User'],
                responses: [
                    HttpResponse::HTTP_OK => new Response(
                        description: 'Register email has been sent.',
                    ),
                    HttpResponse::HTTP_CONFLICT => new Response(
                        description: 'Email address already in use.',
                    ),
                ],
                summary: 'Register using an email',
            ),
        ),
        new Post(
            uriTemplate: '/user/reset',
            status: HttpResponse::HTTP_OK,
            controller: ResetPassword::class,
            openapi: new Operation(
                tags: ['User'],
                responses: [
                    HttpResponse::HTTP_OK => new Response(
                        description: 'Reset password instruction email has been sent.',
                    ),
                    HttpResponse::HTTP_NOT_FOUND => new Response(
                        description: 'Email address not found.',
                    ),
                ],
                summary: 'Request password reset for an account',
            ),
        ),
    ],
)]
final class EmailInput
{
    #[Assert\Email]
    public string $email;
}
