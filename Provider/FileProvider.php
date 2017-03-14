<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Gaufrette\Adapter\Local;
use Gaufrette\Exception\FileNotFound;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * description 
 * @author Donjohn
 */
class FileProvider extends BaseProvider {

    /**
     * @var \Gaufrette\Filesystem
     */
    protected $filesystem;

    public $allowedTypes=array('[a-z]+/[a-z\-]+');

    protected $rootFolder;
    protected $uploadFolder;


    public function __construct($rootFolder, $uploadFolder)
    {

        $this->filesystem = new \Gaufrette\Filesystem(new Local($rootFolder, true, 0775));
        $this->rootFolder = $rootFolder;
        $this->uploadFolder = $uploadFolder;

    }

    public function render( \Twig_Environment $twig, Media $media, $options = array() ) {
        $options['mediaPath'] = $this->getPath($media, isset($options['filter']) ? $options['filter'] : null );
        return parent::render($twig, $media, $options);
    }


    public function getPath(Media $oMedia, $filter= null)
    {
        $firstLevel=100000;
        $secondLevel=1000;

        $rep_first_level = (int) ($oMedia->getId() / $firstLevel);
        $rep_second_level = (int) (($oMedia->getId() - ($rep_first_level * $firstLevel)) / $secondLevel);

        return sprintf('%s/%04s/%02s/%s', $this->uploadFolder, $rep_first_level + 1, $rep_second_level + 1, $oMedia->getFilename() );
    }

    protected function delete(Media $oMedia)
    {
        try {
            return $this->filesystem->delete($this->getPath($oMedia));
        } catch (FileNotFound $e) {
            //do nothing, file already deleted
        }
        return true;
    }


    /**
     * @inheritdoc
     */
    public function prePersist(Media $oMedia)
    {
        $fileName='';
        if ($oMedia->getBinaryContent() instanceof UploadedFile) {
            $fileName = $oMedia->getBinaryContent()->getClientOriginalName();

        } elseif ($oMedia->getBinaryContent() instanceof File) {
            $fileName = $oMedia->getBinaryContent()->getBasename();
        }

        if (empty($fileName)) throw new InvalidMimeTypeException('invalid media');

        $mimeType = $oMedia->getBinaryContent()->getMimeType();
        $this->validateMimeType($mimeType);
        $this->extractMetaData($oMedia);

        $oMedia->setMimeType($mimeType);
        $oMedia->setProviderName($this->getAlias());
        $oMedia->setName($oMedia->getName() ? : $fileName); //to keep oldname
        $oMedia->addMetadata('filename', $fileName);

        $oMedia->setFilename(
            sha1($oMedia->getName() . rand(11111, 99999)) . '.' .
            ($oMedia->getBinaryContent() instanceof \Gaufrette\File
                ? substr($mimeType, strpos($mimeType, '/')+1)
                : $oMedia->getBinaryContent()->guessExtension()));
    }

    /**
     * @inheritdoc
     */
    public function postLoad(Media $oMedia)
    {
        $oMedia->setPaths(array('reference' => $this->getPath($oMedia)));
    }

    /**
     * @inheritdoc
     */
    public function postPersist(Media $oMedia)
    {
        if ($oMedia->getBinaryContent() === null) return false;

        return $this->filesystem->write($this->getPath($oMedia), file_get_contents($oMedia->getBinaryContent()->getRealPath()));

    }

    /**
     * @inheritdoc
     */
    public function postUpdate(Media $oMedia)
    {
        if ($oMedia->getOldMedia() instanceof Media) $this->preRemove($oMedia->getOldMedia());
        return $this->postPersist($oMedia);
    }

    /**
     * @inheritdoc
     */
    public function preRemove(Media $oMedia)
    {
        return $this->delete($oMedia);

    }

    public function extractMetaData(Media $oMedia)
    {
        //Implement extractMetaData() method.
    }

    /**
     * @param Media $oMedia
     * @param array $headers
     * @return StreamedResponse
     */
    public function getDownloadResponse(Media $oMedia, array $headers = array())
    {
        // build the default headers
        $headers = array_merge(array(
            'Content-Type'          => $oMedia->getMimeType(),
            'Content-Disposition'   => sprintf('attachment; filename="%s"', $oMedia->getName()),
        ), $headers);


        $file = $this->filesystem->get($this->getPath($oMedia), true);

        return new StreamedResponse(function () use ($file) {
            echo $file->getContent();
        }, 200, $headers);
    }


}
