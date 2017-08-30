<?php

namespace Donjohn\MediaBundle\Form\Transformer;

use Doctrine\Common\Util\Debug;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Symfony\Component\Form\DataTransformerInterface;
use Donjohn\MediaBundle\Model\Media;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class MediaDataTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    protected $providerAlias;

    /** @var  ProviderFactory $providerFactory */
    protected $providerFactory;

    /**
     * @var string
     */
    protected $classMedia;

    /**
     * MediaDataTransformer constructor.
     * @param ProviderFactory $providerFactory
     * @param null $providerAlias
     * @param string $mediaClass
     */
    public function __construct(ProviderFactory $providerFactory, $providerAlias=null, $classMedia)
    {
        $this->providerAlias = $providerAlias;
        $this->providerFactory = $providerFactory;
        $this->classMedia = $classMedia;
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


        /** @var $oNewMedia Media */
        $oNewMedia = new $this->classMedia();
        $oNewMedia->setBinaryContent($oMedia->getBinaryContent());
        $matches=array();
        $fileName='';

        //si c'est un stream file http://php.net/manual/en/wrappers.data.php
        if (preg_match('#data:(.*);base64,.*#', $oMedia->getBinaryContent(),$matches)) {

            $tmpFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.$oMedia->getOriginalFilename();
            $source = fopen($oMedia->getBinaryContent(), 'r');
            $destination = fopen($tmpFile, 'w');
            stream_copy_to_stream($source, $destination);
            fclose($source);
            fclose($destination);
            $fileName = $oMedia->getOriginalFilename();
            $oNewMedia->setBinaryContent(new File($tmpFile));

            Debug::dump($oNewMedia->getBinaryContent());


        } elseif ($oNewMedia->getBinaryContent() instanceof UploadedFile) {
            $fileName = $oNewMedia->getBinaryContent()->getClientOriginalName();

        } elseif ($oNewMedia->getBinaryContent() instanceof File) {
            $fileName = $oNewMedia->getBinaryContent()->getBasename();
        }

        if (empty($fileName)) {
            throw new TransformationFailedException('invalid media, no filename');
        }

        $oNewMedia->setOriginalFilename($fileName);
        $oNewMedia->setProviderName($this->providerAlias ?: $this->providerFactory->guessProvider($oNewMedia->getBinaryContent())->getProviderAlias());

        try {
            $this->providerFactory->getProvider($oNewMedia->getProviderName())->validateMimeType($oNewMedia->getBinaryContent()->getMimeType());
        } catch (InvalidMimeTypeException $e)
        {
            throw new TransformationFailedException($e->getMessage());
        }




        return $oNewMedia;
    }
}

