<?php
/**
 * @author Donjohn
 * @date 14/10/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Donjohn\MediaBundle\Form\Type\MediaType;

/**
 * Class FormCompilerPass.
 */
class FormCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $resources = $container->getParameter('twig.form.resources');

        $resources[] = '@DonjohnMedia/Form/media_widget.html.twig';

        $container->setParameter('twig.form.resources', $resources);

        if ($container->hasExtension('oneup_uploader')) {
            $definition = $container->getDefinition(MediaType::class);
            $definition->replaceArgument('$filesystemOrphanageStorage', new Reference('oneup_uploader.orphanage.'.$container->getParameter('donjohn.media.one_up.mapping_name')));
        }
    }
}
