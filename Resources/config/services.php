<?php

declare(strict_types=1);

use PetitPress\GpsMessengerBundle\Transport\GpsTransportFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationResolverInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationResolver;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->set(GpsTransportFactory::class)
        ->args([
            new ReferenceConfigurator(GpsConfigurationResolverInterface::class),
            // cache pool will be injected based on the configuration
        ])
        ->tag('messenger.transport_factory')

        ->set(GpsConfigurationResolver::class)

        ->alias(GpsConfigurationResolverInterface::class, GpsConfigurationResolver::class)
    ;
};
