<?php
/**
 * @author jgn
 * @date 04/07/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Twig\Node;

/**
 * Class MediaPathNode.
 */
class MediaPathNode extends \Twig_Node
{
    /**
     * @var string
     */
    protected $extensionName;

    /**
     * PathNode constructor.
     *
     * @param string                $extensionName
     * @param \Twig_Node_Expression $media
     * @param \Twig_Node_Expression $filter
     * @param bool                  $fullPath
     * @param string|null           $lineno
     * @param null                  $tag
     */
    public function __construct(string $extensionName, \Twig_Node_Expression $media, \Twig_Node_Expression $filter, bool $fullPath, $lineno, $tag = null)
    {
        $this->extensionName = $extensionName;

        parent::__construct(array('media' => $media, 'filter' => $filter, 'fullPath' => $fullPath), array(), $lineno, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(\Twig_Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("echo \$this->env->getExtension('%s')->media_path(", $this->extensionName))
            ->subcompile($this->getNode('media'))
            ->raw(', ')
            ->subcompile($this->getNode('filter'))
            ->raw(', ')
            ->subcompile($this->getNode('fullPath'))
            ->raw(");\n")
        ;
    }
}
