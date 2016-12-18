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
     * @var array
     */
    public $allowedTypes=array();


    final public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    final public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @inheritdoc
     */
    public function render(\Twig_Environment $twig, Media $media, $options = array()){
        return $twig->render(sprintf('DonjohnMediaBundle:Provider:media.%s.html.twig', $this->getAlias()),
                            array_merge($options, array('media' => $media))
                            );
    }

    /**
     * {@inheritdoc}
     */
    final public function validateMimeType($type)
    {
        if (count($this->allowedTypes) && !preg_match('#'.implode('|',$this->allowedTypes).'#', $type)) throw new InvalidMimeTypeException(sprintf('provider %s does not support %s, it supports only [%s]', $this->getAlias(), $type, implode(',', $this->allowedTypes)));

        return true;
    }

    public function postLoad(Media $oMedia){}

    public function prePersist(Media $oMedia){}

    public function postPersist(Media $oMedia){}

    public function postUpdate(Media $oMedia){}

    public function postRemove(Media $oMedia){}


}
