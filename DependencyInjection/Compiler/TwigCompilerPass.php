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
        $brandColor = $container->getParameter('easy_admin.design.brand_color');

        $container->getDefinition('twig')->addMethodCall('addGlobal', array('easy_admin_design_brand_color', $brandColor));
    }
}