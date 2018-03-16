<?php

namespace Donjohn\MediaBundle\Form\Transformer;

use Doctrine\Common\Util\ClassUtils;
use Donjohn\MediaBundle\Model\MediaInterface;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class MediaDataTransformer implements DataTransformerInterface
{
    /**
     * @var string $providerAlias
     */
    protected $providerAlias;

    /** @var  ProviderFactory $providerFactory */
    protected $providerFactory;

    /** @var bool $createOnUpdate  */
    protected $createOnUpdate;

    /**
     * MediaDataTransformer constructor.
     * @param ProviderFactory $providerFactory
     * @param null $providerAlias
     * @param boolean $createOnUpdate
     */
    public function __construct(ProviderFactory $providerFactory, $providerAlias=null, $createOnUpdate)
    {
        $this->providerAlias = $providerAlias;
        $this->providerFactory = $providerFactory;
        $this->createOnUpdate = $createOnUpdate;
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
     * @return MediaInterface|mixed|null
     */
    public function reverseTransform($oMedia)
    {
        if (!$oMedia instanceof MediaInterface) return $oMedia;

        // no binary content and no media id return null
        if ($oMedia->getBinaryContent() === null && $oMedia->getId() === null) return null;

        if (!($oMedia instanceof MediaInterface) || (!$oMedia->getBinaryContent())) return $oMedia;


        /** @var $oNewMedia MediaInterface */
        if ($this->createOnUpdate && $oMedia->getId()) {
            $classMedia = ClassUtils::getRealClass($oMedia);
            $oNewMedia = new $classMedia;
            $oNewMedia->setBinaryContent($oMedia->getBinaryContent());
            $oMedia->setBinaryContent(null);
        }
        else {
            $oNewMedia = $oMedia;
        }

        $matches=array();
        $fileName='';

        //si c'est un stream file http://php.net/manual/en/wrappers.data.php
        if (preg_match('#data:(.*);base64,.*#', $oNewMedia->getBinaryContent(),$matches)) {

            $tmpFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.$oNewMedia->getOriginalFilename();
            $source = fopen($oNewMedia->getBinaryContent(), 'rb');
            $destination = fopen($tmpFile, 'wb');
            stream_copy_to_stream($source, $destination);
            fclose($source);
            fclose($destination);
            $fileName = $oNewMedia->getOriginalFilename();
            $newFile = new File($tmpFile);
            $oNewMedia->setBinaryContent($newFile);


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

