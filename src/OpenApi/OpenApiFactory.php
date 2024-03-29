<?php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator('api_platform.openapi.factory')]
class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $this->removeParametersForPathsWithNoParameters($openApi);

        return $openApi;
    }

    private function removeParametersForPathsWithNoParameters(OpenApi $openApi): void
    {
        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            // remove identifier parameter from operations which include "#withoutIdentifier" in the description
            foreach (PathItem::$methods as $method) {
                $getter = 'get'.ucfirst(strtolower($method));
                $setter = 'with'.ucfirst(strtolower($method));
                /** @var ?Operation $operation */
                $operation = $pathItem->$getter();
                $description = $operation?->getDescription();
                if ($operation && $description && str_contains($description, '#withoutIdentifier')) {
                    /** @var Parameter[] $parameters */
                    $parameters = $operation->getParameters();
                    foreach ($parameters as $i => $parameter) {
                        if ('id' === $parameter->getName()) {
                            unset($parameters[$i]);
                            break;
                        }
                    }
                    $description = str_replace('#withoutIdentifier', '', $description);
                    $openApi->getPaths()->addPath($path, $pathItem = $pathItem->$setter($operation->withDescription($description)->withParameters(array_values($parameters))));
                }
            }
        }
    }
}
