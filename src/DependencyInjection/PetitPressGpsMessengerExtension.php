<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\DependencyInjection;

use PetitPress\GpsMessengerBundle\Transport\EncodingStrategy;
use PetitPress\GpsMessengerBundle\Transport\GpsTransportFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

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

        $gpsTransportFactoryDefinition = $container->getDefinition(GpsTransportFactory::class);
        if ($config['auth_cache']) {
            $gpsTransportFactoryDefinition->replaceArgument(1, new Reference($config['auth_cache']));
        }
        if (isset($config['forced_transport'])) {
            $gpsTransportFactoryDefinition->replaceArgument(2, $config['forced_transport']);
        }
        if ($config['encoding_strategy']) {
            $encodingStrategy = EncodingStrategy::from($config['encoding_strategy']);
            $gpsTransportFactoryDefinition->replaceArgument(3, $encodingStrategy);
            if (EncodingStrategy::Wrapped === $encodingStrategy) {
                trigger_deprecation(
                    'petitpress/gps-messenger-bundle',
                    '3.3',
                    'The "wrapped" encoding_strategy is deprecated and will be removed in 4.0. Use "hybrid" if your project is already in production or "flat" for new projects. In 4.0 only the "flat" encoding strategy will be supported and there will be no configuration option to change it.'
                );
            }
        }
    }
}
