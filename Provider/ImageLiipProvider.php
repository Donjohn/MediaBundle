<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Filesystem\MediaFilesystemInterface;
use Donjohn\MediaBundle\Filesystem\MediaLiipLocalFilesystem;
use Donjohn\MediaBundle\Model\Media;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;

/**
 * description.
 *
 * @author Donjohn
 */
class ImageLiipProvider implements ProviderInterface
{
    /** @var ImageProvider $imageProvider */
    protected $imageProvider;

    /**
     * ImageLiipProvider constructor.
     *
     * @param ProviderInterface $imageProvider
     */
    public function __construct(ProviderInterface $imageProvider)
    {
        $this->imageProvider = $imageProvider;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->imageProvider->getAlias();
    }

    /**
     * @param MediaFilesystemInterface $filesystem
     *
     * @return ProviderInterface
     */
    public function setMediaFilesystem(MediaFilesystemInterface $filesystem): ProviderInterface
    {
        return $this->imageProvider->setMediaFilesystem($filesystem);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function validateMimeType(string $type): bool
    {
        return $this->imageProvider->validateMimeType($type);
    }

    /**
     * @param File|null $file
     *
     * @return Guess
     */
    public function guess(File $file = null): Guess
    {
        return $this->imageProvider->guess($file);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function addProviderOptions(array $options): array
    {
        return $this->imageProvider->addProviderOptions($options);
    }

    /**
     * @param \Twig_Environment $twig
     *
     * @return ProviderInterface
     */
    public function setTwig(\Twig_Environment $twig): ProviderInterface
    {
        return $this->imageProvider->setTwig($twig);
    }

    /**
     * @param Media $media
     */
    public function extractMetaData(Media $media): void
    {
        $this->imageProvider->extractMetaData($media);
    }

    /**
     * @param Media $media
     */
    public function postLoad(Media $media): void
    {
        $this->imageProvider->postLoad($media);
    }

    /**
     * @param Media $media
     */
    public function prePersist(Media $media): void
    {
        $this->imageProvider->prePersist($media);
    }

    /**
     * @param Media $media
     */
    public function postPersist(Media $media): void
    {
        $this->imageProvider->postPersist($media);
    }

    /**
     * @param Media $media
     */
    public function preUpdate(Media $media): void
    {
        $this->imageProvider->preUpdate($media);
    }

    /**
     * @param Media $media
     */
    public function postUpdate(Media $media): void
    {
        $this->imageProvider->postUpdate($media);
    }

    /**
     * @param Media $media
     */
    public function preRemove(Media $media): void
    {
        $this->imageProvider->preRemove($media);
    }

    /**
     * @param FormInterface $form
     * @param array         $options
     */
    public function addEditForm(FormInterface $form, array $options): void
    {
        $this->imageProvider->addEditForm($form, $options);
    }

    /**
     * @param FormInterface $form
     * @param array         $options
     */
    public function addCreateForm(FormInterface $form, array $options): void
    {
        $this->imageProvider->addCreateForm($form, $options);
    }

    /**
     * @return array
     */
    public function getAllowedTypes(): array
    {
        return $this->imageProvider->getAllowedTypes();
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->imageProvider->getTemplate();
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig(): \Twig_Environment
    {
        return $this->imageProvider->getTwig();
    }

    /**
     * @return MediaLiipLocalFilesystem
     */
    public function getMediaFilesystem(): MediaFilesystemInterface
    {
        return $this->imageProvider->getMediaFilesystem();
    }

    /**
     * @return mixed
     */
    public function getFileMaxSize()
    {
        return $this->imageProvider->getFileMaxSize();
    }

    /**
     * @param Media       $media
     * @param string|null $filter
     * @param array       $options
     *
     * @return string
     */
    public function render(Media $media, string $filter = null, array $options = array()): string
    {
        return $this->getTwig()->render($this->getTemplate(),
            array('mediaPath' => $this->getMediaFilesystem()->getPath($media, $filter),
                    'name' => $media->getName(),
                'options' => $options, )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadResponse(Media $media, array $headers = array(), string $filter = null): Response
    {
        // build the default headers
        $headers = array_merge(array(
            'Content-Type' => $media->getMimeType(),
            'Content-Disposition' => sprintf('attachment; filename="%s"', $media->getName()),
        ), $headers);

        return new BinaryFileResponse($this->getMediaFilesystem()->getFullPath($media, $filter), 200, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(Media $media, string $filter = null, bool $fullPath = false): string
    {
        return $fullPath ?
            $this->getMediaFilesystem()->getWebPath($media, $filter) :
            $this->getMediaFilesystem()->getPath($media, $filter);
    }
}
