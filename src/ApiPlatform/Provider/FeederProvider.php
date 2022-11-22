<?php

declare(strict_types=1);

namespace App\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PartialPaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Entity\AlnFeeder;
use App\Repository\AlnFeederRepository;

/**
 * @implements ProviderInterface<AlnFeeder>
 */
final class FeederProvider implements ProviderInterface
{
    public function __construct(
        private readonly AlnFeederRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return AlnFeeder|PartialPaginatorInterface<AlnFeeder>|iterable<AlnFeeder>|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AlnFeeder|PartialPaginatorInterface|iterable|null
    {
        return $this->repository->find($uriVariables['id'] ?? null);
    }
}
