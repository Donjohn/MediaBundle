<?php

namespace Donjohn\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;

/**
 * Class ProviderCompilerPass.
 */
class ProviderCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds(
            'media.provider'
        );

        foreach ($taggedServices as $providerId => $tagAttributes) {
            $container->getDefinition(ProviderFactory::class)
                        ->addMethodCall('addProvider', array(new Reference($providerId)));

            $providerDefinition = $container->getDefinition($providerId);
            $providerDefinition->addMethodCall('setTwig', [new Reference('twig')]);
        }
    }
}
