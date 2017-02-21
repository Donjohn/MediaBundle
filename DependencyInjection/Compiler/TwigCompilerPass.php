<?php
/**
 * @author jgn
 * @date 21/02/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $brandColor = $container->getParameter('media_dropzone_border_color');

        $container->getDefinition('twig')->addMethodCall('addGlobal', array('media_dropzone_border_color', $brandColor));
    }
}