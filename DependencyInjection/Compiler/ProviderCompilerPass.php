<?php
namespace Donjohn\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ProviderCompilerPass implements CompilerPassInterface
{
    /**
     * 
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        
        $definition = $container->getDefinition(
            'donjohn.media.provider.factory'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'media.provider'
        );


        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addProvider',
                    array(new Reference($id), $attributes['alias'])
                );
                $definitionProvider = $container->getDefinition($id);
                $definitionProvider->addMethodCall('setTemplate', array(
                    $container->hasParameter('donjohn.media.provider.'.$attributes['alias'].'.template') ?
                        $container->getParameter('donjohn.media.provider.'.$attributes['alias'].'.template') :
                        'DonjohnMediaBundle:Provider:media.'.$attributes['alias'].'.html.twig')
                );
            }
        }
    }
}
