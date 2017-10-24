<?php

namespace OJezu\DynamicParameterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('ojezu_dynamic_parameter');

        $root
            ->children()
                ->scalarNode('multi_installation')
                    ->defaultValue(false)
                ->end()
                ->arrayNode('advanced_parameters')
                    ->children()
                        ->arrayNode('json_provider')
                            ->children()
                                ->scalarNode('file_path')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('processor')
                            ->children()
                                ->scalarNode('load_configuration')->defaultValue(true)->end()
                                ->arrayNode('provider')
                                    ->children()
                                        ->scalarNode('service')
                                            ->cannotBeEmpty()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('parameter_map')
                                    ->useAttributeAsKey('name')
                                    ->prototype('array')
                                        ->children()
                                            ->arrayNode('path')
                                                ->isRequired()
                                                ->cannotBeEmpty()
                                                ->prototype('scalar')->end()
                                            ->end()
                                            ->variableNode('default')->end()
                                            ->variableNode('no_config_value')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
