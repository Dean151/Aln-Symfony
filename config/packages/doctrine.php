<?php

declare(strict_types=1);

use App\Dbal\Types\AlnTimeType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('doctrine', ['dbal' => ['host' => '%env(resolve:MYSQL_HOST)%', 'port' => '%env(resolve:MYSQL_TCP_PORT)%', 'user' => '%env(resolve:DATABASE_USER)%', 'password' => '%env(resolve:DATABASE_PASSWORD)%', 'dbname' => '%env(resolve:DATABASE_NAME)%', 'server_version' => 'mariadb-10.8.3', 'types' => ['aln_time' => AlnTimeType::class]], 'orm' => ['auto_generate_proxy_classes' => true, 'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware', 'auto_mapping' => true, 'mappings' => ['App' => ['is_bundle' => false, 'dir' => '%kernel.project_dir%/src/Entity', 'prefix' => 'App\Entity', 'alias' => 'App']]]]);
    if ('test' === $containerConfigurator->env()) {
        $containerConfigurator->extension('doctrine', [
            'dbal' => [
                'dbname_suffix' => '_test%env(default::TEST_TOKEN)%',
            ],
        ]);
    }
    if ('prod' === $containerConfigurator->env()) {
        $containerConfigurator->extension('doctrine', [
            'orm' => [
                'auto_generate_proxy_classes' => false,
                'query_cache_driver' => [
                    'type' => 'pool',
                    'pool' => 'doctrine.system_cache_pool',
                ],
                'result_cache_driver' => [
                    'type' => 'pool',
                    'pool' => 'doctrine.result_cache_pool',
                ],
            ],
        ]);
        $containerConfigurator->extension('framework', [
            'cache' => [
                'pools' => [
                    'doctrine.result_cache_pool' => [
                        'adapter' => 'cache.app',
                    ],
                    'doctrine.system_cache_pool' => [
                        'adapter' => 'cache.system',
                    ],
                ],
            ],
        ]);
    }
};