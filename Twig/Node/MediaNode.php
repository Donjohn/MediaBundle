<?php
/**
 * @author Donjohn
 * @date 04/07/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Twig\Node;

/**
 * Class MediaNode.
 */
class MediaNode extends \Twig_Node
{
    /**
     * @var string
     */
    protected $extensionName;

    /**
     * @param string                $extensionName
     * @param \Twig_Node_Expression $media
     * @param \Twig_Node_Expression $filter
     * @param \Twig_Node_Expression $attributes
     * @param int                   $lineno
     * @param string                $tag
     */
    public function __construct(string $extensionName, \Twig_Node_Expression $media, \Twig_Node_Expression $filter, \Twig_Node_Expression $attributes, int $lineno, string $tag = null)
    {
        $this->extensionName = $extensionName;

        parent::__construct(array('media' => $media, 'filter' => $filter, 'attributes' => $attributes), array(), $lineno, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(\Twig_Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("echo \$this->env->getExtension('%s')->media(", $this->extensionName))
            ->subcompile($this->getNode('media'))
            ->raw(', ')
            ->subcompile($this->getNode('filter'))
            ->raw(', ')
            ->subcompile($this->getNode('attributes'))
            ->raw(");\n")
        ;
    }
}
