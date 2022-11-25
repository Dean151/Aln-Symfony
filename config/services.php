<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->load('App\\', __DIR__.'/../src/')
        ->exclude([
            __DIR__.'/../src/DependencyInjection/',
            __DIR__.'/../src/Entity/',
            __DIR__.'/../src/Kernel.php', ]
        );

    if ('test' === $containerConfigurator->env()) {
        $services->set(UserPasswordHasher::class)
            ->autowire()
            ->public();
    }
};
