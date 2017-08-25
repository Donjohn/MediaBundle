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
                ->arrayNode('providers')
                    ->useAttributeAsKey('provider_name')
                    ->prototype('array')
                       ->children()
                            ->scalarNode('template')->end()
                            ->scalarNode('allowed_mimetype')->end()
                        ->end()
                    ->end()->defaultValue(['file' => ['template' => 'DonjohnMediaBundle:Provider:media.file.html.twig', 'allowed_types' => ['[a-z]+/[a-z\-]+']],
                                            'image' => ['template' => 'DonjohnMediaBundle:Provider:media.image.html.twig', 'allowed_types'=> ['image/bmp', 'image/gif', 'image/jpeg', 'image/jpg', 'image/png', 'image/tiff', 'image/jpeg', 'image/png']] ])
                ->end()
                ->scalarNode('upload_folder')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('fine_uploader_template')->defaultValue('DonjohnMediaBundle:Form:fine_uploader_template.html.twig')->end()
            ->end();

        return $treeBuilder;
    }
}
