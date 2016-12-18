<?php

namespace Donjohn\MediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DonjohnMediaExtension extends Extension
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
        $container->setParameter('donjohn.media.entities', array_keys($config['entities']));

        //until liiPbundle 2.0 is released, i need this filter
        if (isset($bundles['LiipImagineBundle']) && !class_exists('Liip\ImagineBundle\Imagine\Filter\Loader\ScaleFilterLoader'))
            $loader->load('imagine.yml');

        //delcaration des services api pour ttoues les entites dans la conf
        if (isset($bundles['DunglasApiBundle'])){
            foreach ($config['entities'] as $classMedia => $configClassMedia) {
                $container->setDefinition(sprintf('donjohn.media.api.resource.%s', $classMedia), $this->createApiService($classMedia, $configClassMedia));
                $container->setDefinition(sprintf('donjohn.media.api.listener.%s', $classMedia), $this->createApiListenerService($container->getDefinition('donjohn.media.provider.factory')));
            }
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

}
