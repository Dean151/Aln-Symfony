<?php

declare(strict_types=1);

namespace App\ApiResource\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\AlnFeeder;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Temporary provider to fix an issue starting from ApiPlatform 3.1.11
 * See https://github.com/api-platform/core/issues/6014 for more info.
 *
 * @implements ProviderInterface<AlnFeeder>
 */
final class AlnFeederProvider implements ProviderInterface
{
    /**
     * @param ProviderInterface<AlnFeeder> $itemProvider
     */
    public function __construct(
        #[Autowire('@api_platform.doctrine.orm.state.item_provider')]
        private readonly ProviderInterface $itemProvider,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AlnFeeder
    {
        $feeder = $this->itemProvider->provide($operation, $uriVariables, $context);
        if (is_null($feeder)) {
            return null;
        }

        assert($feeder instanceof AlnFeeder);

        return $feeder;
    }
}
