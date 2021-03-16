<?php

namespace Programgames\OroDev\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class OroDevConfig implements ConfigurationInterface
{
    /** @noinspection NullPointerExceptionInspection */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('orodev');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('service')
                    ->children()
                        ->arrayNode('postgres')
                            ->children()
                                ->scalarNode('user')->end()
                                ->scalarNode('password')->end()
                                ->scalarNode('start_command')->end()
                                ->scalarNode('stop_command')->end()
                                ->scalarNode('restart_command')->end()
                                ->scalarNode('version_command')->end()
                                ->scalarNode('logs_command')->end()
                            ->end()
                        ->end()
                        ->arrayNode('mailcatcher')
                            ->children()
                                ->scalarNode('start_command')->end()
                                ->scalarNode('stop_command')->end()
                                ->scalarNode('restart_command')->end()
                                ->scalarNode('open_command')->end()
                            ->end()
                        ->end()
                        ->arrayNode('rabbitmq')
                            ->children()
                                ->scalarNode('start_command')->end()
                                ->scalarNode('stop_command')->end()
                                ->scalarNode('restart_command')->end()
                                ->scalarNode('version_command')->end()
                                ->scalarNode('user')->end()
                                ->scalarNode('password')->end()
                                ->scalarNode('open_command')->end()
                                ->scalarNode('logs_command')->end()
                                ->scalarNode('config_file')->end()
                            ->end()
                        ->end()
                        ->arrayNode('elasticsearch')
                            ->children()
                                ->scalarNode('start_command')->end()
                                ->scalarNode('stop_command')->end()
                                ->scalarNode('restart_command')->end()
                                ->scalarNode('version_command')->end()
                                ->scalarNode('logs_command')->end()
                                ->scalarNode('config_file')->end()
                                ->scalarNode('user')->end()
                                ->scalarNode('password')->end()
                            ->end()
                        ->end()
                        ->arrayNode('kibana')
                            ->children()
                                ->scalarNode('start_command')->end()
                                ->scalarNode('stop_command')->end()
                                ->scalarNode('restart_command')->end()
                                ->scalarNode('version_command')->end()
                                ->scalarNode('open_command')->end()
                                ->scalarNode('config_file')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
