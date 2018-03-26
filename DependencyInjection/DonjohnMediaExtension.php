<?php

namespace Donjohn\MediaBundle\DependencyInjection;

use Donjohn\MediaBundle\Provider\ProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
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

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        //on init les params
        $container->setParameter('donjohn.media.file_max_size', $config['file_max_size']);
        $container->setParameter('donjohn.media.chunk_size', $config['chunk_size']);
        $container->setParameter('donjohn.media.providers.config', array_merge($config['providers'], $config['providers_ext']) );
        $container->setParameter('donjohn.media.fine_uploader.template', $config['fine_uploader_template'] );
        $container->setParameter('donjohn.media.root_folder', $container->getParameter('kernel.project_dir').'/web');
        if (Kernel::VERSION_ID > 40000) $container->setParameter('donjohn.media.root_folder', $container->getParameter('kernel.project_dir').'/public');

        if (array_key_exists('OneupUploaderBundle', $container->getParameter('kernel.bundles'))) {
            $loader->load('oneup_uploader.yml');
        }
        if (array_key_exists('ApiPlatformBundle', $container->getParameter('kernel.bundles'))) {
            $loader->load('api_platform.yml');
        }
        if (array_key_exists('LiipImagineBundle', $container->getParameter('kernel.bundles'))) {
            $loader->load('liip_imagine.yml');
        }


        $container->registerForAutoconfiguration(ProviderInterface::class)->addTag('media.provider');

    }


}
