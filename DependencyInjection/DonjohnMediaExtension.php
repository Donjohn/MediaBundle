<?php

namespace Donjohn\MediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DonjohnMediaExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('donjohn.media.upload_folder', $config['upload_folder']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        //on init les params
        $container->setParameter('donjohn.media.entity', $config['entity']);
        $container->setParameter('donjohn.media.file_max_size', $config['file_max_size']);
        $container->setParameter('donjohn.media.chunk_size', $config['chunk_size']);
        $container->setParameter('donjohn.media.providers.templates', array_map(function($item) {return $item['template'];}, $config['providers']) );
        $container->setParameter('donjohn.media.fine_uploader.template', $config['fine_uploader_template'] );


    }


    public function prepend(ContainerBuilder $container)
    {
        $config = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(
            $container->getExtension($this->getAlias())->getConfiguration($config, $container),
            $config
        );
        $upload_folder = $config['upload_folder'];

        if ($container->hasExtension('easy_admin')) {
            $config = $container->getExtensionConfig('easy_admin');
            $config = $this->processConfiguration(
                $container->getExtension('easy_admin')->getConfiguration($config, $container),
                $config
            );

            $container->setParameter('media_mediazone_border_color', $config['design']['brand_color']);
        } else {
            $container->setParameter('media_mediazone_border_color', '#205081');
        }



        $config = $container->getExtensionConfig('liip_imagine');
        $config = $this->processConfiguration(
            $container->getExtension('liip_imagine')->getConfiguration($config, $container),
            $config
        );

        if (isset($config['loaders']['default']['filesystem']['data_root']))
        {
            $config['loaders']['default']['filesystem']['data_root'] = [$upload_folder];
        }

        if (!isset($config['filter_sets']['thumbnail']['filters']['thumbnail']['size'][0])) {
            throw new MissingMandatoryParametersException('you shall define the thumbnail in liip_imagine config part (check DonjohnMediaBundle documentation)');
        } else {

            $container->setParameter('media_mediazone_thumbnail_height', $config['filter_sets']['thumbnail']['filters']['thumbnail']['size'][0]);
        }
    }


}
