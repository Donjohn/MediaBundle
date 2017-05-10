<?php

namespace Donjohn\MediaBundle;

use Donjohn\MediaBundle\DependencyInjection\Compiler\FormCompilerPass;
use Donjohn\MediaBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Donjohn\MediaBundle\DependencyInjection\Compiler\TwigCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DonjohnMediaBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if (class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass'))
            $container->addCompilerPass( DoctrineOrmMappingsPass::createAnnotationMappingDriver( array('Donjohn\MediaBundle\Model') , array(realpath(__DIR__.DIRECTORY_SEPARATOR.'Model')) ));

        $bundles = $container->getParameter('kernel.bundles');

        $container->addCompilerPass(new ProviderCompilerPass());
        $container->addCompilerPass(new FormCompilerPass(isset($bundles['OneupUploaderBundle'])));
        $container->addCompilerPass(new TwigCompilerPass());

    }
}
