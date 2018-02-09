<?php
/**
 * @author jgn
 * @date 04/07/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Twig\Node;


class PathNode extends \Twig_Node
{
    protected $extensionName;

    /**
     * PathNode constructor.
     * @param string $extensionName
     * @param \Twig_Node_Expression $media
     * @param \Twig_Node_Expression $filter
     * @param null|string $lineno
     * @param null $tag
     */
    public function __construct($extensionName, \Twig_Node_Expression $media, \Twig_Node_Expression $filter, $lineno, $tag = null)
    {
        $this->extensionName = $extensionName;

        parent::__construct(array('media' => $media, 'filter' => $filter), array(), $lineno, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("echo \$this->env->getExtension('%s')->path(", $this->extensionName))
            ->subcompile($this->getNode('media'))
            ->raw(', ')
            ->subcompile($this->getNode('filter'))
            ->raw(");\n")
        ;
    }
}
