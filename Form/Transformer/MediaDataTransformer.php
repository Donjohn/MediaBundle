<?php

namespace Donjohn\MediaBundle\Form\Transformer;

use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Donjohn\MediaBundle\Provider\ProviderInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Donjohn\MediaBundle\Model\Media;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class MediaDataTransformer implements DataTransformerInterface
{
    /**
     * @var \Donjohn\MediaBundle\Provider\ProviderInterface
     */
    protected $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($oMedia)
    {
        if (!$oMedia instanceof Media) return $oMedia;

        // no binary content and no media id return null
        if (empty($oMedia->getBinaryContent()) && $oMedia->getId() === null) return null;

        if (!($oMedia instanceof Media) || (!$oMedia->getBinaryContent() instanceof \SplFileInfo)) return $oMedia;


        $matches=array(); $fileName='';

        //si c'est un stream file http://php.net/manual/en/wrappers.data.php
        if (preg_match('#data:('.implode('|', $this->provider->allowedTypes).');base64,.*#', $oMedia->getBinaryContent(),$matches)) {
            $tmpFile = tempnam(sys_get_temp_dir(), $this->provider->getAlias());
            $source = fopen($oMedia->getBinaryContent(), 'r');
            $destination = fopen($tmpFile, 'w');
            stream_copy_to_stream($source, $destination);
            fclose($source);
            fclose($destination);
            $oMedia->setBinaryContent(new UploadedFile($tmpFile, basename($tmpFile), $matches[1]));
        }

        if ($oMedia->getBinaryContent() instanceof UploadedFile) {
            $fileName = $oMedia->getBinaryContent()->getClientOriginalName();

        } elseif ($oMedia->getBinaryContent() instanceof File) {
            $fileName = $oMedia->getBinaryContent()->getBasename();
        }

        if (empty($fileName)) throw new TransformationFailedException('invalid media');

        try {
            $this->provider->validateMimeType($oMedia->getBinaryContent()->getMimeType());
        } catch (InvalidMimeTypeException $e)
        {
            throw new TransformationFailedException($e->getMessage());
        }

        $oMedia->setProviderName($this->provider->getAlias());


        return $oMedia;
    }
}

