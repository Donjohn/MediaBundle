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
    protected $alias;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    public $allowedTypes;

    /**
     * @param $template
     * @return $this
     */
    final public function setTemplate($template)
    {
        if (empty($template)) throw new \InvalidArgumentException('please configure a template name for '.$this->getAlias().' provider');
        $this->template = $template;
        return $this;
    }

    /**
     * @param array $allowedTypes
     * @return $this
     */
    final public function setAllowedTypes(array $allowedTypes)
    {
        if (empty($allowedTypes)) throw new \InvalidArgumentException('please configure allowed_types for '.$this->getAlias().' provider');
        $this->allowedTypes = $allowedTypes;
        return $this;
    }

    /**
     * @return string
     */
    final public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return string
     */
    abstract public function getAlias();

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
