<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * description 
 * @author Donjohn
 */
interface ProviderInterface {

    /**
     * @param string $template template name
     */
    public function setTemplate($template);

    /**
     * @return string template
     */
    public function getTemplate();

    /**
     * @param string $alias provider alias
     */
    public function setAlias($alias);

    /**
     * @return string alias
     */
    public function getAlias();
    
    /**
     * validate the mimeType of the file
     * @throws InvalidMimeTypeException
     */
    public function validateMimeType($type);

    /**
     * @param \Twig_Environment $twig_Environment
     * @param \Donjohn\MediaBundle\Model\Media $media
     * @return string
     */
    public function render(\Twig_Environment $twig_Environment, Media $media, $options = array());


    /**
     * extract data from media, size/height/etc..;
     * @param Media $oMedia
     * @return array metadatas
     */
    public function extractMetaData(Media $oMedia);

    /**
     * function called on postLoad Dcotrine Event on Media entity
     * @param Media $oMedia
     */
    public function postLoad(Media $oMedia);


    /**
     * function called on prePersist Dcotrine Event on v entity
     * @param Media $oMedia
     */
    public function prePersist(Media $oMedia);

    /**
     * function called on postPersist Dcotrine Event on Media entity
     * @param Media $oMedia
     */
    public function postPersist(Media $oMedia);

    /**
     * function called on postUpdate Dcotrine Event on Media entity
     * @param Media $oMedia
     */
    public function postUpdate(Media $oMedia);

    /**
     * function called on preRemove Dcotrine Event on Media entity
     * @param Media $oMedia
     */
    public function preRemove(Media $oMedia);

    /**
     * add edit fields for the defined provider
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return mixed
     */
    public function addEditForm(FormBuilderInterface $builder, array $options);

    /**
     * add create fields for the defined provider
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return mixed
     */
    public function addCreateForm(FormBuilderInterface $builder, array $options);



    /**
     * return path of the media, depends on the media ^^
     * @param \Donjohn\MediaBundle\Model\Media $oMedia
     * @return mixed
     */
    public function getPath(Media $oMedia, $filter= null);

    /**
     * return the full path of the media on the server, depends on the media ^^
     * @param \Donjohn\MediaBundle\Model\Media $oMedia
     * @return mixed
     */
    public function getFullPath(Media $oMedia, $filter= null);

    /**
     * return response for each media according to provider
     * @param Media $oMedia
     * @return Response
     */
    public function getDownloadResponse(Media $oMedia);
    
}
