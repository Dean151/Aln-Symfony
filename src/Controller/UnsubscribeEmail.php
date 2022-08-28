<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
final class UnsubscribeEmail extends AbstractController
{
    #[Route(path: 'email/unsubscribe', name: 'app_email_unsubscribe', methods: ['POST'])]
    public function unsubscribe(): Response
    {
        // TODO: if we send non-mandatory email, this route should unsubscribe them
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
