<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('petit_press_gps_messenger');
        // Keep compatibility with symfony/config < 4.2
        if (!method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->root('petit_press_gps_messenger');
        } else {
            $rootNode = $treeBuilder->getRootNode();
        }

        $rootNode
            ->children()
                ->scalarNode('auth_cache')
                    ->cannotBeEmpty()
                    ->defaultValue('cache.app')
                    ->info('A cache for storing access tokens.')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
