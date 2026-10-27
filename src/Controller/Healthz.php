<?php

declare(strict_types=1);

namespace App\Controller;

use App\Health\HealthCheckInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class Healthz extends AbstractController
{
    /**
     * @param iterable<string, HealthCheckInterface> $checks
     */
    public function __construct(
        #[AutowireIterator(HealthCheckInterface::class, indexAttribute: 'key')]
        private readonly iterable $checks,
    ) {
    }

    #[Route('/healthz', name: 'healthz', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $results = [];
        foreach ($this->checks as $name => $check) {
            $results[$name] = $check->isHealthy() ? 'ok' : 'fail';
        }
        $healthy = !in_array('fail', $results, true);

        $response = $this->json(
            ['status' => $healthy ? 'ok' : 'fail', 'checks' => $results],
            $healthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE,
        );
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
