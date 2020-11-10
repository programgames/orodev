<?php

namespace Programgames\OroDev\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class OroDevConfig implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('orodev');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('postgres')
                    ->children()
                        ->variableNode('password')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
