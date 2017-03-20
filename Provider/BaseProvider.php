<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;


/**
 * description 
 * @author Donjohn
 */
abstract class BaseProvider implements ProviderInterface {

    /**
     * @var string
     */
    protected $alias='';

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    public $allowedTypes=array();

    /**
     * @param $template
     * @return $this
     */
    final public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
   final  public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $alias
     * @return $this
     */
    final public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    final public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @inheritdoc
     */
    public function render(\Twig_Environment $twig, Media $media, $options = array()){
        return $twig->render($this->getTemplate(),
                            array_merge($options, array('media' => $media))
                            );
    }

    /**
     * {@inheritdoc}
     */
    final public function validateMimeType($type)
    {
        if (count($this->allowedTypes) && !preg_match('#'.implode('|',$this->allowedTypes).'#', $type)) throw new InvalidMimeTypeException(sprintf('%s is not supported', $type));

        return true;
    }


}
