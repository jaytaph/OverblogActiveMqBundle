<?php

namespace Overblog\StompBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('overblog_stomp');

        $rootNode
            ->children()
                ->arrayNode('connections')
                    ->addDefaultsIfNotSet()
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->defaultValue('localhost')->end()
                            ->scalarNode('port')->defaultValue(61613)->end()
                            ->scalarNode('user')->defaultNull()->end()
                            ->scalarNode('password')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('publishers')
                    ->addDefaultsIfNotSet()
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('connection')->defaultValue('default')->end()
                            ->arrayNode('options')
                                ->children()
                                    ->scalarNode('type')->defaultValue('queue')->end()
                                    ->scalarNode('name')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('consumers')
                    ->addDefaultsIfNotSet()
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('connection')->defaultValue('default')->end()
                            ->scalarNode('handler')->isRequired()->end()
                            ->arrayNode('options')
                                ->children()
                                    ->scalarNode('type')->defaultValue('queue')->end()
                                    ->scalarNode('name')->isRequired()->end()
                                    ->scalarNode('prefetchSize')->defaultNull()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            //Connection validation
            ->validate()
                ->ifTrue( function($v) {
                    foreach($v['publishers'] as $key => $producer)
                    {
                        if(!isset($v['connections'][$producer['connection']])) return true;
                    }

                    return false;
                })
                ->thenInvalid('Unknow connection in publishers configuration.')
            ->end()
            //Connection validation
            ->validate()
                ->ifTrue( function($v) {
                    foreach($v['consumers'] as $key => $producer)
                    {
                        if(!isset($v['connections'][$producer['connection']])) return true;
                    }

                    return false;
                })
                ->thenInvalid('Unknow connection in consumers configuration.')
            ->end()
        ;

        return $treeBuilder;
    }
}
