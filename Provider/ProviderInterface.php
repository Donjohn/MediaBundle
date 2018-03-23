<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Filesystem\MediaFilesystemInterface;
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

    /**
     * @param array $allowedTypes
     * @return mixed
     */
    public function setAllowedTypes(array $allowedTypes);

    /**
     * @param MediaFilesystemInterface $filesystem
     * @return mixed
     */
    public function setMediaFilesystem(MediaFilesystemInterface $filesystem);
    
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
     * @param Media $media
     * @param null $filter
     * @param array $options
     * @return mixed     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function render(Media $media, $filter = null, array $options = array());


    /**
     * extract data from media, size/height/etc..;
     * @param Media $media
     * @return array metadata
     */
    public function extractMetaData(Media $media);

    /**
     * function called on postLoad Doctrine Event on Media entity
     * @param Media $media
     */
    public function postLoad(Media $media);


    /**
     * function called on prePersist Doctrine Event on v entity
     * @param Media $media
     */
    public function prePersist(Media $media);

    /**
     * function called on postPersist Doctrine Event on Media entity
     * @param Media $media
     */
    public function postPersist(Media $media);

    /**
     * function called on preUpdate Doctrine Event on Media entity
     * @param Media $media
     */
    public function preUpdate(Media $media);

    /**
     * function called on postUpdate Doctrine Event on Media entity
     * @param Media $media
     */
    public function postUpdate(Media $media);

    /**
     * function called on preRemove Doctrine Event on Media entity
     * @param Media $media
     */
    public function preRemove(Media $media);

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
     * return response for each media according to provider
     * @param Media $media
     * @return Response
     */
    public function getDownloadResponse(Media $media, array $headers = array());

}
