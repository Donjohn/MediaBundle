<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Model\MediaInterface;
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
     * @param \Twig_Environment $twig
     * @return mixed
     */
    public function setTwig(\Twig_Environment $twig);


    /**
     * @param MediaInterface $media
     * @param null $filter
     * @param array $options
     * @return mixed     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function render(MediaInterface $media, $filter = null, array $options = array());


    /**
     * extract data from media, size/height/etc..;
     * @param MediaInterface $oMedia
     * @return array metadata
     */
    public function extractMetaData(MediaInterface $oMedia);

    /**
     * function called on postLoad Doctrine Event on Media entity
     * @param MediaInterface $oMedia
     */
    public function postLoad(MediaInterface $oMedia);


    /**
     * function called on prePersist Doctrine Event on v entity
     * @param MediaInterface $oMedia
     */
    public function prePersist(MediaInterface $oMedia);

    /**
     * function called on postPersist Doctrine Event on Media entity
     * @param MediaInterface $oMedia
     */
    public function postPersist(MediaInterface $oMedia);

    /**
     * function called on preUpdate Doctrine Event on Media entity
     * @param MediaInterface $oMedia
     */
    public function preUpdate(MediaInterface $oMedia);

    /**
     * function called on postUpdate Doctrine Event on Media entity
     * @param MediaInterface $oMedia
     */
    public function postUpdate(MediaInterface $oMedia);

    /**
     * function called on preRemove Doctrine Event on Media entity
     * @param MediaInterface $oMedia
     */
    public function preRemove(MediaInterface $oMedia);

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
     * @param MediaInterface $oMedia
     * @param string|null $filter
     * @return mixed
     */
    public function getPath(MediaInterface $oMedia, $filter= null);

    /**
     * return the full path of the media on the server, depends on the media ^^
     * @param MediaInterface $oMedia
     * @param string|null $filter
     * @return mixed
     */
    public function getFullPath(MediaInterface $oMedia, $filter= null);

    /**
     * return response for each media according to provider
     * @param MediaInterface $oMedia
     * @return Response
     */
    public function getDownloadResponse(MediaInterface $oMedia);

}
