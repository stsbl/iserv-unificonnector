<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use IServ\Library\IdmApiClient\IdmClient;
use IServ\Library\IdmApiClient\IdmClientInterface;

// This file is the entry point to configure your own services.
// Files in the packages/ subdirectory configure your dependencies.

return static function (ContainerConfigurator $configurator): void {
    // default configuration for services in *this* file
    $services = $configurator->services()
        ->defaults()
        ->autowire()      // Automatically injects dependencies in your services.
        ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
    ;

    $configurator
        ->parameters()
        ->set('env(POSTGRES_VERSION)', '11.10') // We set a fallback value which normally will be set from the outside
    ;

    // makes classes in src/ available to be used as services
    // this creates a service per class whose id is the fully-qualified class name
    $services->load('IServ\\UnifiConnector\\', '../src/*')
        ->exclude(['../src/{DependencyInjection,Entity,Tests}/', '../src/Kernel.php'])
    ;

    $services->set(\UniFi_API\Client::class)
        ->factory([service(\IServ\UnifiConnector\Unifi\ApiClientFactory::class), 'createApiClient'])
        ->lazy()
    ;

    $services->set(IdmClient::class)
        ->args([
            '$baseUrl' => 'http://localhost:987/',
            '$credentials' => null,
            '$defaultHeaders' => ['User-Agent' => 'IServ/UniFiConnector'],
        ])
    ;

    $services->set(\IServ\UnifiConnector\OAuth\OAuthIdmClient::class)
        ->arg('$client', service(IdmClient::class));
    $services->alias(IdmClientInterface::class, \IServ\UnifiConnector\OAuth\OAuthIdmClient::class);

    $services->alias(\IServ\UnifiConnector\Host\HostRepository::class, \IServ\UnifiConnector\Host\HostApiRepository::class);
};
