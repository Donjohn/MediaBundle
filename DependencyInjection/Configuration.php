<?php
/**
 * @author jgn
 * @date 09/09/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('donjohn_media');

        $rootNode
            ->children()
                ->arrayNode('entities')
                    ->cannotBeEmpty()
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('group_input')
                                ->cannotBeEmpty()
                                ->useAttributeAsKey('id')
                                ->prototype('scalar')->end()
                                ->defaultValue(array('api_input'))
                            ->end()
                            ->arrayNode('group_output')
                                ->cannotBeEmpty()
                                ->useAttributeAsKey('id')
                                ->prototype('scalar')->end()
                                ->defaultValue(array('api_output'))
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('upload_folder')->isRequired()->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
}
