<?php
/**
 * @author Donjohn
 * @date 09/09/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\DependencyInjection;


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
        $rootNode = $treeBuilder->root('donjohn_media');

        $rootNode
            ->children()
                ->scalarNode('file_max_size')->defaultValue(ini_get('upload_max_filesize'))->cannotBeEmpty()->end()
                ->scalarNode('chunk_size')->defaultValue(ini_get('upload_max_filesize'))->cannotBeEmpty()->end()
                ->arrayNode('providers')->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('file')->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('template')->defaultValue('DonjohnMediaBundle:Provider:media.file.html.twig')->end()
                                ->scalarNode('allowed_types')->defaultValue([])->end()
                                ->scalarNode('enabled')->defaultValue(true)->end()
                            ->end()
                        ->end()
                        ->arrayNode('image')->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('template')->defaultValue('DonjohnMediaBundle:Provider:media.image.html.twig')->end()
                                ->scalarNode('allowed_types')->defaultValue(['image/bmp', 'image/gif', 'image/jpeg', 'image/jpg', 'image/png', 'image/tiff', 'image/jpeg', 'image/png'])->end()
                                ->scalarNode('enabled')->defaultValue(true)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('providers_ext')
                    ->prototype('array')
                       ->children()
                            ->scalarNode('template')->end()
                            ->scalarNode('allowed_types')->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('upload_folder')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('fine_uploader_template')->defaultValue('DonjohnMediaBundle:Form:fine_uploader_template.html.twig')->end()
            ->end();

        return $treeBuilder;
    }
}
