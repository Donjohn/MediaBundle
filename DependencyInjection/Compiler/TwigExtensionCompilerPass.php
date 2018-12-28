<?php
/**
 * @author Donjohn
 * @date 14/10/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\DependencyInjection\Compiler;

use Donjohn\MediaBundle\Serializer\MediaNormalizer;
use Donjohn\MediaBundle\Twig\Extension\MediaApiExtension;
use Donjohn\MediaBundle\Twig\Extension\MediaExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class FormCompilerPass.
 */
class TwigExtensionCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasExtension('api_platform')) {
            $definition = $container->getDefinition(MediaExtension::class);
            $definition->replaceArgument('$normalizer', $container->getDefinition(MediaNormalizer::class));
        }
    }
}
