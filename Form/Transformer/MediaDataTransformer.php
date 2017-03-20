<?php

namespace Donjohn\MediaBundle\Form\Transformer;

use Doctrine\Common\Util\Debug;
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

    /**
     * @var string
     */
    protected $mediaClass;

    public function __construct(ProviderInterface $provider, $mediaClass)
    {
        $this->provider = $provider;
        $this->mediaClass = $mediaClass;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value;
    }


    /**
     * @param mixed $oMedia
     * @return Media|mixed|null
     */
    public function reverseTransform($oMedia)
    {
        if (!$oMedia instanceof Media) return $oMedia;

        // no binary content and no media id return null
        if (empty($oMedia->getBinaryContent()) && $oMedia->getId() === null) return null;

        if (!($oMedia instanceof Media) || (!$oMedia->getBinaryContent())) return $oMedia;

        $oMedia->setProviderName( $oMedia->getProviderName() ? $oMedia->getProviderName() : $this->provider->getAlias() );

        /** @var $oNewMedia Media */
        $oNewMedia = new $this->mediaClass();
        $oNewMedia->setProviderName($oMedia->getProviderName())
                    ->setBinaryContent($oMedia->getBinaryContent());
        $matches=array();
        $fileName='';

        //si c'est un stream file http://php.net/manual/en/wrappers.data.php
        if (preg_match('#data:(.*);base64,.*#', $oMedia->getBinaryContent(),$matches)) {

            $tmpFile = tempnam(sys_get_temp_dir(), $this->provider->getAlias());
            $source = fopen($oMedia->getBinaryContent(), 'r');
            $destination = fopen($tmpFile, 'w');
            stream_copy_to_stream($source, $destination);
            fclose($source);
            fclose($destination);
            $fileName = $oMedia->getOriginalFilename();
            $oNewMedia->setBinaryContent(new UploadedFile($tmpFile, $fileName, $matches[1]));


        } elseif ($oNewMedia->getBinaryContent() instanceof UploadedFile) {
            $fileName = $oNewMedia->getBinaryContent()->getClientOriginalName();

        } elseif ($oNewMedia->getBinaryContent() instanceof File) {
            $fileName = $oNewMedia->getBinaryContent()->getBasename();
        }

        $oNewMedia->setOriginalFilename($fileName);
        $oNewMedia->setProviderName($this->provider->getAlias());

        if (empty($fileName)) {
            throw new TransformationFailedException('invalid media, no filename');
        }

        try {
            $this->provider->validateMimeType($oNewMedia->getBinaryContent()->getMimeType());
        } catch (InvalidMimeTypeException $e)
        {
            throw new TransformationFailedException($e->getMessage());
        }




        return $oNewMedia;
    }
}

