<?php
/**
 * @author jgn
 * @date 04/07/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Twig\Node;


class DownloadNode extends \Twig_Node
{
    protected $extensionName;

    /**
     * @param string                 $extensionName
     * @param \Twig_Node_Expression $media
     * @param \Twig_Node_Expression $filter
     * @param \Twig_Node_Expression $attributes
     * @param int                   $lineno
     * @param string                $tag
     */
    public function __construct($extensionName, \Twig_Node_Expression $media, $lineno, $tag = null)
    {
        $this->extensionName = $extensionName;

        parent::__construct(array('media' => $media), array(), $lineno, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("echo \$this->env->getExtension('%s')->download(", $this->extensionName))
            ->subcompile($this->getNode('media'))
            ->raw(");\n")
        ;
    }
}
