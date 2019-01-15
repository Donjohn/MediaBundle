<?php

namespace Donjohn\MediaBundle\DependencyInjection;

use Donjohn\MediaBundle\Provider\ProviderInterface;
use Donjohn\MediaBundle\Routing\Loader\DonjohnMediaLoader;
use Donjohn\MediaBundle\Uploader\Naming\OriginalNamer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\Kernel;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DonjohnMediaExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        //on init les params

        $container->setParameter('donjohn.media.upload_folder', $config['upload_folder']);
        $container->setParameter('donjohn.media.file_max_size', $config['file_max_size']);
        $container->setParameter('donjohn.media.chunk_size', $config['chunk_size']);
        $container->setParameter('donjohn.media.fine_uploader.template', $config['fine_uploader_template']);
        $container->setParameter('donjohn.media.one_up.mapping_name', $config['mapping_name']);
        $container->setParameter('donjohn.media.root_folder', $container->getParameter('kernel.project_dir').DIRECTORY_SEPARATOR.'web');
        if (Kernel::VERSION_ID > 40000) {
            $container->setParameter('donjohn.media.root_folder', $container->getParameter('kernel.project_dir').'/public');
        }

        if (array_key_exists('OneupUploaderBundle', $container->getParameter('kernel.bundles'))) {
            $loader->load('oneup_uploader.yml');

            $definition = new Definition(DonjohnMediaLoader::class);
            $definition->setArgument('$mapping_name', $config['mapping_name']);
            $definition->addTag('routing.loader');
            $container->setDefinition(DonjohnMediaLoader::class, $definition);
        }
        if (array_key_exists('ApiPlatformBundle', $container->getParameter('kernel.bundles'))) {
            $loader->load('api_platform.yml');
        }
        if (array_key_exists('LiipImagineBundle', $container->getParameter('kernel.bundles'))) {
            $loader->load('liip_imagine.yml');
        }
        if (array_key_exists('HautelookAliceBundle', $container->getParameter('kernel.bundles'))) {
            $container->setParameter('donjohn.media.fixture_folder', $container->getParameter('kernel.project_dir').$config['fixture_folder']);
            $loader->load('hautelook_alice.yml');
        }


        $container->registerForAutoconfiguration(ProviderInterface::class)->addTag('media.provider');
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('oneup_uploader')) {
            return;
        }
        //on recupere le mapping name
        $configs = $container->getExtensionConfig($this->getAlias());
        $configMediaBundle = $this->processConfiguration(new Configuration(), $configs);
        $mappingName = $configMediaBundle['mapping_name'];

        $configs = $container->getExtensionConfig('oneup_uploader');
        foreach ($configs as $config) {
            if (!isset($config['mappings'])) {
                continue;
            }
            foreach ($config['mappings'] as $key => $param) {
                $mappings[$key] = $param;
            }
        }

        $mappings[$mappingName] = ['namer' => OriginalNamer::class, 'use_orphanage' => true, 'frontend' => 'fineuploader'];

        $container->prependExtensionConfig('oneup_uploader', [
            'chunks' => ['storage' => ['directory' => '%kernel.cache_dir%/uploader/chunks']],
            'mappings' => $mappings,
        ]);
    }
}
