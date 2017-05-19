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
                ->scalarNode('file_max_size')->defaultValue(ini_get('upload_max_filesize'))->end()
                ->scalarNode('chunk_size')->defaultValue('50M')->end()
                ->scalarNode('entity')->cannotBeEmpty()->end()
                ->arrayNode('api')
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
                ->arrayNode('providers')
                    ->useAttributeAsKey('provider_name')
                    ->prototype('array')
                       ->children()
                            ->scalarNode('template')->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('upload_folder')->isRequired()->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
}
