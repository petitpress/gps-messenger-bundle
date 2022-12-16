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
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('auth_cache')
                    ->cannotBeEmpty()
                    ->defaultValue('cache.app')
                    ->info('A cache for storing access tokens.')
                ->end()
                ->enumNode('forced_transport')
                    ->values(['grpc', 'rest'])
                    ->defaultNull()
                    ->info('A forced transport for all messenger transports.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
