<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\DependencyInjection;

use PetitPress\GpsMessengerBundle\Transport\GpsTransportFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class PetitPressGpsMessengerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if ($config['auth_cache']) {
            $gpsTransportFactoryDefinition = $container->getDefinition(GpsTransportFactory::class);
            $gpsTransportFactoryDefinition->replaceArgument(1, new Reference($config['auth_cache']));
        }
    }
}
