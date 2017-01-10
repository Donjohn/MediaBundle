<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Gaufrette\Adapter\Local;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * description 
 * @author Donjohn
 */
class FileProvider extends BaseProvider {

    /**
     * @var \Gaufrette\Filesystem
     */
    protected $filesystem;

    public $allowedTypes=array('[a-z]+/[a-z]+');

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

    /**
     * @inheritdoc
     */
    public function transform(Media $oMedia)
    {
        $matches=array(); $fileName='';

        //si c'est un stream file http://php.net/manual/en/wrappers.data.php
        if (preg_match('#data:('.implode('|', $this->allowedTypes).');base64,.*#', $oMedia->getBinaryContent(),$matches)) {
            $tmpFile = tempnam(sys_get_temp_dir(), $this->getAlias());
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

        $mimeType = $oMedia->getBinaryContent()->getMimeType();
        try {
            $this->extractMetaData($oMedia);
            $this->validateMimeType($mimeType);
        } catch (InvalidMimeTypeException $e)
        {
            throw new TransformationFailedException($e->getMessage());
        }

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
        if ($oMedia->getOldMedia() instanceof Media) $this->filesystem->delete($this->getPath($oMedia->getOldMedia()));
        return $this->postPersist($oMedia);
    }

    /**
     * @inheritdoc
     */
    public function postRemove(Media $oMedia)
    {
        return $this->filesystem->delete($this->getPath($oMedia));
    }

    public function extractMetaData(Media $oMedia)
    {
        //Implement extractMetaData() method.
    }

    public function addEditForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('binaryContent', FileType::class, array('required' => false, 'translation_domain' => 'DonjohnMediaBundle', 'label' => 'media.'.$this->getAlias().'.binaryContent'));
    }

    public function addCreateForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('binaryContent', FileType::class, array('translation_domain' => 'DonjohnMediaBundle', 'label' => 'media.'.$this->getAlias().'.binaryContent') );
    }


}
