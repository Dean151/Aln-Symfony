<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator
        ->extension('api_platform', [
            'title' => 'Aln-Symfony',
            'description' => 'More infos on Github: https://github.com/Dean151/Aln-Symfony',
            'version' => '0.2.4',
            'show_webby' => false,
            'mapping' => [
                'paths' => [
                    '%kernel.project_dir%/src/Entity',
                    '%kernel.project_dir%/src/ApiPlatform/Dto',
                ],
            ],
            'formats' => [
                'json' => ['application/json'],
                'html' => ['text/html'],
            ],
            'patch_formats' => [
                'json' => ['application/merge-patch+json'],
            ],
            'swagger' => [
                'api_keys' => [
                    'JWT' => [
                        'name' => 'Authorization',
                        'type' => 'header',
                    ],
                ],
                'versions' => [3],
            ],
            'defaults' => [
                'normalization_context' => [
                    'skip_null_values' => false,
                ],
            ],
        ]);
};
