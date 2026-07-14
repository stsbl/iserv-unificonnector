<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use IServ\Library\IdmApiClient\IdmClient;
use IServ\Library\IdmApiClient\IdmClientInterface;
use IServ\Library\Zeit\Clock\Clock;
use IServ\Library\Zeit\Clock\SystemClock;
use IServ\UnifiConnector\OAuth\OAuthCredentials;
use Psr\Clock\ClockInterface;

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

    $services->set(SystemClock::class)
        ->factory([SystemClock::class, 'create']);
    $services->alias(Clock::class, SystemClock::class);
    $services->alias(ClockInterface::class, SystemClock::class);

    $services->set(\UniFi_API\Client::class)
        ->factory([service(\IServ\UnifiConnector\Unifi\ApiClientFactory::class), 'createApiClient'])
        ->lazy()
    ;

    $services->set(IdmClient::class)
        ->args([
            '$baseUrl' => 'http://localhost:987/',
            '$credentials' => service(OAuthCredentials::class),
            '$defaultHeaders' => ['User-Agent' => 'IServ/UniFiConnector'],
        ])
    ;

    $services->alias(IdmClientInterface::class, IdmClient::class);

    $services->alias(\IServ\UnifiConnector\Host\HostRepository::class, \IServ\UnifiConnector\Host\HostApiRepository::class);

    if ('test' === $configurator->env()) {
        $services->set(\IServ\Library\Config\Config::class)
            ->args([['Servername' => 'iserv.test']]);
        $services->set(\IServ\Bundle\Autocomplete\Endpoint\Domain\Endpoints::class)
            ->factory([\IServ\Bundle\Autocomplete\Endpoint\Domain\Endpoints::class, 'fromArray'])
            ->args([[
                'autocomplete' => '/autocomplete',
                'lookup' => '/autocomplete/lookup',
                'learn' => '/autocomplete/learn',
            ]]);
        $services->set(\IServ\Bundle\Autocomplete\Endpoint\StaticEndpointsProvider::class)
            ->arg('$endpoints', service(\IServ\Bundle\Autocomplete\Endpoint\Domain\Endpoints::class));
        $services->alias(\IServ\Bundle\Autocomplete\Endpoint\EndpointsProviderInterface::class, \IServ\Bundle\Autocomplete\Endpoint\StaticEndpointsProvider::class);
    }
};
