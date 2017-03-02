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
     * {@inheritdoc}
     */
    public function reverseTransform($oMedia)
    {
        if (!$oMedia instanceof Media) return $oMedia;

        // no binary content and no media id return null
        if (empty($oMedia->getBinaryContent()) && $oMedia->getId() === null) return null;

        if (!($oMedia instanceof Media) || (!$oMedia->getBinaryContent())) return $oMedia;

        $oNewMedia = new $this->mediaClass();
        $oNewMedia->setProviderName($oMedia->getProviderName())
                    ->setBinaryContent($oMedia->getBinaryContent());
        $matches=array();
        $fileName='';

        //si c'est un stream file http://php.net/manual/en/wrappers.data.php

        if (preg_match('#data:('.implode('|', $this->provider->allowedTypes).');base64,.*#', $oMedia->getBinaryContent(),$matches)) {

            //^data:([a-zA-Z]+/[a-zA-Z]+);base64\,([a-zA-Z0-9+\=/]+)$
            //passer le filename dans le champ en plus sinon t'es baisé

            $tmpFile = tempnam(sys_get_temp_dir(), $this->provider->getAlias());
            $source = fopen($oMedia->getBinaryContent(), 'r');
            $destination = fopen($tmpFile, 'w');
            stream_copy_to_stream($source, $destination);
            fclose($source);
            fclose($destination);
            $oNewMedia->setBinaryContent(new UploadedFile($tmpFile, basename($tmpFile), $matches[1]));
        }

        if ($oNewMedia->getBinaryContent() instanceof UploadedFile) {
            $fileName = $oNewMedia->getBinaryContent()->getClientOriginalName();

        } elseif ($oNewMedia->getBinaryContent() instanceof File) {
            $fileName = $oNewMedia->getBinaryContent()->getBasename();
        }

        if (empty($fileName)) throw new TransformationFailedException('invalid media');

        try {
            $this->provider->validateMimeType($oNewMedia->getBinaryContent()->getMimeType());
        } catch (InvalidMimeTypeException $e)
        {
            throw new TransformationFailedException($e->getMessage());
        }

        $oNewMedia->setProviderName($this->provider->getAlias());


        return $oNewMedia;
    }
}

