<?php

namespace Donjohn\MediaBundle;

use Donjohn\MediaBundle\DependencyInjection\Compiler\FormCompilerPass;
use Donjohn\MediaBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Donjohn\MediaBundle\DependencyInjection\Compiler\TwigExtensionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class DonjohnMediaBundle.
 */
class DonjohnMediaBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        if (class_exists(DoctrineOrmMappingsPass::class)) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createAnnotationMappingDriver(array('Donjohn\MediaBundle\Model'), array(realpath(__DIR__.DIRECTORY_SEPARATOR.'Model'))));
        }

        $container->addCompilerPass(new ProviderCompilerPass());
        $container->addCompilerPass(new FormCompilerPass());
        $container->addCompilerPass(new TwigExtensionCompilerPass());
    }
}
