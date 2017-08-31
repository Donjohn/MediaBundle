<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Donjohn\MediaBundle\Provider\Guesser\ProviderGuess;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\HttpFoundation\File\File;
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
     * @return string alias
     */
    public function getAlias();


    public function setAllowedTypes(array $allowedTypes);
    
    /**
     * validate the mimeType of the file
     * @param string $type
     * @throws InvalidMimeTypeException
     */
    public function validateMimeType($type);

    /**
     * @param null|File $file
     * @return ProviderGuess|null|Guess
     */
    public function guess($file = null);

    /**
     * @param \Twig_Environment $twig_Environment
     * @param Media $media
     * @param null $filter
     * @param array $options
     * @return string
     */
    public function render(\Twig_Environment $twig_Environment, Media $media, $filter = null, $options = array());


    /**
     * extract data from media, size/height/etc..;
     * @param Media $oMedia
     * @return array metadata
     */
    public function extractMetaData(Media $oMedia);

    /**
     * function called on postLoad Doctrine Event on Media entity
     * @param Media $oMedia
     */
    public function postLoad(Media $oMedia);


    /**
     * function called on prePersist Doctrine Event on v entity
     * @param Media $oMedia
     */
    public function prePersist(Media $oMedia);

    /**
     * function called on postPersist Doctrine Event on Media entity
     * @param Media $oMedia
     */
    public function postPersist(Media $oMedia);

    /**
     * function called on preUpdate Doctrine Event on Media entity
     * @param Media $oMedia
     */
    public function preUpdate(Media $oMedia);

    /**
     * function called on postUpdate Doctrine Event on Media entity
     * @param Media $oMedia
     */
    public function postUpdate(Media $oMedia);

    /**
     * function called on preRemove Doctrine Event on Media entity
     * @param Media $oMedia
     */
    public function preRemove(Media $oMedia);

    /**
     * add edit fields for the defined provider
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return mixed
     */
    public function addEditForm(FormBuilderInterface $builder, array $options);

    /**
     * add create fields for the defined provider
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return mixed
     */
    public function addCreateForm(FormBuilderInterface $builder, array $options);



    /**
     * return path of the media, depends on the media ^^
     * @param \Donjohn\MediaBundle\Model\Media $oMedia
     * @param string|null $filter
     * @return mixed
     */
    public function getPath(Media $oMedia, $filter= null);

    /**
     * return the full path of the media on the server, depends on the media ^^
     * @param \Donjohn\MediaBundle\Model\Media $oMedia
     * @param string|null $filter
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
