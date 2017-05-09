<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Filesystem\MediaLocalFilesystem;
use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Gaufrette\Exception\FileNotFound;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
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

    protected $fileMaxSize;


    final public function __construct(MediaLocalFilesystem $filesystem, $fileMaxSize)
    {

        $this->filesystem = $filesystem;
        $this->fileMaxSize = $fileMaxSize;

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

        return sprintf('%04s/%02s/%s', $rep_first_level + 1, $rep_second_level + 1, $oMedia->getFilename() );
    }

    public function getFullPath(Media $oMedia, $filter = null)
    {
        return $this->filesystem->getWebFolder().DIRECTORY_SEPARATOR.$this->getPath($oMedia, $filter);
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

        $oMedia->setMd5(md5(file_get_contents($oMedia->getBinaryContent()->getRealPath())));
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
        if ($oMedia->getBinaryContent() instanceof UploadedFile || $oMedia->getBinaryContent() instanceof File) {
            $newPath = $this->getFullPath($oMedia);
            return $oMedia->getBinaryContent()->move(dirname($newPath),basename($newPath));
        } else {
            return $this->filesystem->write($this->getPath($oMedia), file_get_contents($oMedia->getBinaryContent()->getRealPath()));
        }



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

    public function addEditForm(FormBuilderInterface $builder, array $options)
    {
        $options['constraints'] = array(new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => $this->fileMaxSize
                    ]));
        $builder->add('binaryContent', FileType::class, $options );
    }

    public function addCreateForm(FormBuilderInterface $builder, array $options)
    {
        $options['constraints'] = array(new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => $this->fileMaxSize
                    ]));
        $builder->add('binaryContent', FileType::class, $options );
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
