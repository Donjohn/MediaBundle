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
        $taggedServices = $container->findTaggedServiceIds(
            'media.provider'
        );

        foreach ($taggedServices as $providerId => $tagAttributes) {
            $container->getDefinition('donjohn.media.provider.factory')
                        ->addMethodCall('addProvider', array(new Reference($providerId)) );

            $providerDefinition = $container->getDefinition($providerId);
            $providerDefinition->addMethodCall('setTwig', [new Reference('twig')]);
        }
    }
}
