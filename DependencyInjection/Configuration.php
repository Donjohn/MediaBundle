<?php
/**
 * @author Donjohn
 * @date 09/09/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('donjohn_media');

        $rootNode
            ->children()
                ->scalarNode('file_max_size')->defaultValue(ini_get('upload_max_filesize'))->end()
                ->scalarNode('chunk_size')->defaultValue(ini_get('upload_max_filesize'))->end()
                ->scalarNode('upload_folder')->defaultValue('/media')->end()
                ->scalarNode('mapping_name')->defaultValue('donjohn_media')->end()
                ->scalarNode('fine_uploader_template')->defaultValue('DonjohnMediaBundle:Form:fine_uploader_template.html.twig')->end()
                ->scalarNode('fixture_folder')->defaultValue('/assets/fixtures/img')->end()
            ->end();

        return $treeBuilder;
    }
}
