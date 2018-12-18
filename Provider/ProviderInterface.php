<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Filesystem\MediaFilesystemInterface;
use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Guesser\ProviderGuess;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;

/**
 * description.
 *
 * @author Donjohn
 */
interface ProviderInterface
{
    /**
     * @return string alias
     */
    public function getAlias(): string;

    /**
     * @param MediaFilesystemInterface $filesystem
     *
     * @return mixed
     */
    public function setMediaFilesystem(MediaFilesystemInterface $filesystem): ProviderInterface;

    /**
     * @return MediaFilesystemInterface
     */
    public function getMediaFilesystem(): MediaFilesystemInterface;

    /**
     * @param string $type
     *
     * @return bool
     */
    public function validateMimeType(string $type): bool;

    /**
     * @param null|File $file
     *
     * @return ProviderGuess|null|Guess
     */
    public function guess(File $file = null): Guess;

    /**
     * @param array $options
     *
     * @return array
     */
    public function addProviderOptions(array $options): array;

    /**
     * @param \Twig_Environment $twig
     *
     * @return mixed
     */
    public function setTwig(\Twig_Environment $twig): ProviderInterface;

    /**
     * @return \Twig_Environment
     */
    public function getTwig(): \Twig_Environment;

    /**
     * @param Media       $media
     * @param string|null $filter
     * @param array       $options
     *
     * @return string
     */
    public function render(Media $media, string $filter = null, array $options = array()): string;

    /**
     * extract data from media, size/height/etc..;.
     *
     * @param Media $media
     */
    public function extractMetaData(Media $media): void;

    /**
     * function called on postLoad Doctrine Event on Media entity.
     *
     * @param Media $media
     */
    public function postLoad(Media $media): void;

    /**
     * function called on prePersist Doctrine Event on v entity.
     *
     * @param Media $media
     */
    public function prePersist(Media $media): void;

    /**
     * function called on postPersist Doctrine Event on Media entity.
     *
     * @param Media $media
     */
    public function postPersist(Media $media): void;

    /**
     * function called on preUpdate Doctrine Event on Media entity.
     *
     * @param Media $media
     */
    public function preUpdate(Media $media): void;

    /**
     * function called on postUpdate Doctrine Event on Media entity.
     *
     * @param Media $media
     */
    public function postUpdate(Media $media): void;

    /**
     * function called on preRemove Doctrine Event on Media entity.
     *
     * @param Media $media
     */
    public function preRemove(Media $media): void;

    /**
     * add edit fields for the defined provider.
     *
     * @param FormInterface $form
     * @param array         $options
     *
     * @return mixed
     */
    public function addEditForm(FormInterface $form, array $options): void;

    /**
     * add create fields for the defined provider.
     *
     * @param FormInterface $form
     * @param array         $options
     *
     * @return mixed
     */
    public function addCreateForm(FormInterface $form, array $options): void;

    /**
     * @return array
     */
    public function getAllowedTypes(): array;

    /**
     * @return string
     */
    public function getTemplate(): string;

    /**
     * return response for each media according to provider.
     *
     * @param Media      $media
     * @param array|null $headers
     *
     * @return Response
     */
    public function getDownloadResponse(Media $media, array $headers = array()): Response;
}
