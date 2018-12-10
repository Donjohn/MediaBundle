<?php

namespace Donjohn\MediaBundle\Form\Transformer;

use Doctrine\Common\Util\ClassUtils;
use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaDataTransformer implements DataTransformerInterface
{
    /** @var ProviderFactory $providerFactory */
    protected $providerFactory;

    /** @var boolean $createOnUpdate */
    protected $createOnUpdate;

    /** @var string $providerName*/
    protected $providerName;

    /**
     * MediaDataTransformer constructor.
     *
     * @param ProviderFactory $providerFactory
     * @param bool            $createOnUpdate
     */
    public function __construct(ProviderFactory $providerFactory, bool $createOnUpdate, string  $providerName)
    {
        $this->providerFactory = $providerFactory;
        $this->createOnUpdate = $createOnUpdate;
        $this->providerName = $providerName;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * @param mixed $media
     *
     * @return Media|mixed|null
     */
    public function reverseTransform($media)
    {
        if (!$media instanceof Media) {
            return $media;
        }

        // no binary content and no media id return null
        if (null === $media->getBinaryContent() && null === $media->getId()) {
            return null;
        }

        if (!($media instanceof Media) || (!$media->getBinaryContent())) {
            return $media;
        }

        /* @var $newMedia Media */
        if ($this->createOnUpdate && $media->getId()) {
            $classMedia = ClassUtils::getRealClass($media);
            $newMedia = new $classMedia();
            $newMedia->setBinaryContent($media->getBinaryContent());
            $media->setBinaryContent(null);
        } else {
            $newMedia = $media;
        }

        $matches = array();
        $fileName = '';

        //si c'est un stream file http://php.net/manual/en/wrappers.data.php
        if (preg_match('#data:(.*);base64,.*#', $newMedia->getBinaryContent(), $matches)) {
            if (null === $newMedia->getOriginalFilename()) {
                throw new TransformationFailedException('invalid media, no filename');
            }

            $tmpFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.$newMedia->getOriginalFilename();
            $source = fopen($newMedia->getBinaryContent(), 'rb');
            $destination = fopen($tmpFile, 'wb');
            stream_copy_to_stream($source, $destination);
            fclose($source);
            fclose($destination);
            $fileName = $newMedia->getOriginalFilename();
            $newFile = new File($tmpFile);
            $newMedia->setBinaryContent($newFile);
        } elseif ($newMedia->getBinaryContent() instanceof UploadedFile) {
            $fileName = $newMedia->getBinaryContent()->getClientOriginalName();
        } elseif ($newMedia->getBinaryContent() instanceof File) {
            $fileName = $newMedia->getBinaryContent()->getBasename();
        }

        if (null === $fileName) {
            throw new TransformationFailedException('invalid media, no filename');
        }

        $newMedia->setOriginalFilename($fileName);

        if (null === $newMedia->getProviderName()) {
            $newMedia->setProviderName($this->providerName ?? $this->providerFactory->guessProvider($newMedia->getBinaryContent())->getProviderAlias());
        }

        try {
            $this->providerFactory->getProvider($newMedia->getProviderName())->validateMimeType($newMedia->getBinaryContent()->getMimeType());
        } catch (InvalidMimeTypeException $e) {
            throw new TransformationFailedException('invalid media, no filename');
        }

        return $newMedia;
    }
}
