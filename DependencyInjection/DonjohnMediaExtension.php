<?php

namespace Donjohn\MediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
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

        $bundles = $container->getParameter('kernel.bundles');

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('provider.yml');
        $loader->load('doctrine.yml');
        $loader->load('form.yml');
        $loader->load('twig.yml');

        //on sauve la liste des entites media
        $container->setParameter('donjohn.media.entity', $config['entity']);
        $container->setParameter('donjohn.media.file_max_size', $config['file_max_size']);
        $container->setParameter('donjohn.media.chunk_size', $config['chunk_size']);

        if (isset($config['providers']) && count($config['providers'])){
            foreach ($config['providers'] as $providerAlias => $configProvider) {
                $container->setParameter('donjohn.media.provider.'.$providerAlias.'.template', $configProvider['template']);
            }

        }


        //delcaration des services api pour toutes les entites dans la conf
        if (isset($bundles['DunglasApiBundle'])){
            $container->setDefinition(sprintf('donjohn.media.api.resource.%s', $config['entity']), $this->createApiService($config['entity'], $config['api']));
            $container->setDefinition(sprintf('donjohn.media.api.listener.%s', $config['entity']), $this->createApiListenerService($container->getDefinition('donjohn.media.provider.factory')));
        }

        //delcaration des services api pour toutes les entites dans la conf
        if (isset($bundles['OneupUploaderBundle'])){
            $loader->load('oneup_uploader.yml');
        }

    }


    /**
     * @param $classMedia
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    protected function createApiService($classMedia, $configClassMedia)
    {
        $definition = new Definition('Dunglas\ApiBundle\Api\Resource', array($classMedia));
        $definition
                ->addMethodCall('initNormalizationContext', array(array('groups' => $configClassMedia['group_output'])))
                ->addMethodCall('initDenormalizationContext', array(array('groups' => $configClassMedia['group_input'])))
                ->addTag('api.resource');
        return $definition;
    }

    /**
     * @param $classMedia
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    protected function createApiListenerService($providerFactoryClass)
    {
        $definition = new Definition('Donjohn\MediaBundle\Listener\ApiListener', array($providerFactoryClass));
        $definition->addTag('kernel.event_listener', array('event' => 'api.pre_create', 'method' => 'onPreCreate'));
        $definition->addTag('kernel.event_listener', array('event' => 'api.post_create', 'method' => 'onPostCreate'));
        return $definition;
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
