<?php
namespace Donjohn\MediaBundle\DependencyInjection\Compiler;

use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Donjohn\MediaBundle\Provider\ProviderInterface;
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
            ProviderFactory::class
        );

        $taggedServices = $container->findTaggedServiceIds(
            'media.provider'
        );


        foreach ($taggedServices as $id => $tagAttributes) {
            if ($id instanceof ProviderInterface) {
                foreach ($tagAttributes as $attributes) {
                    $definition->addMethodCall(
                        'addProvider',
                        array(new Reference($id))
                    );
                    $container->get($id)->setTemplate( $container->hasParameter('donjohn.media.provider.'.$container->get($id)->getAlias().'.template') );

                }
            }
        }
    }
}
