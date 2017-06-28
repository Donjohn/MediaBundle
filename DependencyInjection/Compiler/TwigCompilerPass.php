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
        $container->getDefinition('twig')->addMethodCall('addGlobal', array('media_mediazone_border_color', $container->getParameter('media_mediazone_border_color')));
        $container->getDefinition('twig')->addMethodCall('addGlobal', array('media_mediazone_thumbnail_height', $container->getParameter('media_mediazone_thumbnail_height')));
    }
}
