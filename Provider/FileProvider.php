<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Filesystem\MediaFilesystemInterface;
use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * description 
 * @author Donjohn
 */
class FileProvider extends BaseProvider {

    /**
     * @var MediaFilesystemInterface
     */
    protected $filesystem;

    /** @var string $fileMaxSize */
    protected $fileMaxSize;

    /**
     * FileProvider constructor.
     * @param MediaFilesystemInterface $filesystem
     * @param string $uploadFolder
     * @param string $fileMaxSize
     */
    public function __construct(MediaFilesystemInterface $filesystem, $fileMaxSize)
    {
        $this->filesystem = $filesystem;
        $this->fileMaxSize = $fileMaxSize;

    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return 'file';
    }


    /**
     * @inheritdoc
     */
    public function prePersist(Media $media)
    {
        $fileName='';
        if ($media->getBinaryContent() instanceof UploadedFile) {
            $fileName = $media->getBinaryContent()->getClientOriginalName();

        } elseif ($media->getBinaryContent() instanceof File) {
            $fileName = $media->getBinaryContent()->getBasename();
        }

        if (empty($fileName)) throw new InvalidMimeTypeException('invalid media');

        if ($media->getBinaryContent() !== null )  {

            $media->setFilename( sha1($media->getName() . random_int(11111, 99999)) . '.' . pathinfo($media->getOriginalFilename(), PATHINFO_EXTENSION) );

            if(stripos(PHP_OS, 'WIN') === 0)
                $media->setMd5(md5_file($media->getBinaryContent()->getRealPath()));
            else {
                $output = shell_exec('md5sum -b ' . escapeshellarg($media->getBinaryContent()->getRealPath()));
                $media->setMd5(substr($output,0,strpos($output,' ')+1));
            }
        }

        $mimeType = $media->getBinaryContent()->getMimeType();
        $this->validateMimeType($mimeType);
        $this->extractMetaData($media);

        $media->setMimeType($mimeType);
        $media->setProviderName($this->getAlias());
        $media->setName($media->getName() ? : $fileName); //to keep oldname
        $media->addMetadata('filename', $fileName);

    }

    /**
     * @inheritdoc
     */
    public function postLoad(Media $media)
    {
        $media->setPaths(array('reference' => $this->filesystem->getWebPath($media)));
    }

    /**
     * @inheritdoc
     */
    public function postPersist(Media $media)
    {
        if ($media->getBinaryContent() instanceof File) {
            $this->filesystem->createMedia($media, $media->getBinaryContent());
            $media->setBinaryContent(null);
        }
        $this->postLoad($media);
    }

    /**
     * @inheritdoc
     */
    public function preUpdate(Media $media)
    {
        if ($media->getBinaryContent() !== null )  {

            $media->setFilename( sha1($media->getName() . random_int(11111, 99999)) . '.' . pathinfo($media->getOriginalFilename(), PATHINFO_EXTENSION) );

            if(stripos(PHP_OS, 'WIN') === 0)
                $media->setMd5(md5_file($media->getBinaryContent()->getRealPath()));
            else {
                $output = shell_exec('md5sum -b ' . escapeshellarg($media->getBinaryContent()->getRealPath()));
                $media->setMd5(substr($output,0,strpos($output,' ')+1));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function postUpdate(Media $media)
    {
        $this->postPersist($media);
        $oldMedia = $media->oldMedia();
        if ($oldMedia instanceof Media) $this->preRemove($oldMedia);

    }

    /**
     * @inheritdoc
     */
    public function preRemove(Media $media)
    {
        return $this->filesystem->removeMedia($media);

    }

    /**
     * @inheritdoc
     */
    public function extractMetaData(Media $media)
    {
        //Implement extractMetaData() method.
    }

    /**
     * @inheritdoc
     */
    public function addEditForm(FormBuilderInterface $builder, array $options)
    {
        $options['constraints'] = array(new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => $this->fileMaxSize
                    ]));
        $builder->add('binaryContent', FileType::class, $options );
    }

    /**
     * @inheritdoc
     */
    public function addCreateForm(FormBuilderInterface $builder, array $options)
    {
        $options['constraints'] = array(new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => $this->fileMaxSize
                    ]));
        $builder->add('binaryContent', FileType::class, $options );
    }



    /**
     * @inheritdoc
     */
    public function getDownloadResponse(Media $media, array $headers = array())
    {
        // build the default headers
        $headers = array_merge(array(
            'Content-Type'          => $media->getMimeType(),
            'Content-Disposition'   => sprintf('attachment; filename="%s"', $media->getName()),
        ), $headers);


        return new BinaryFileResponse($this->filesystem->getFullPath($media), 200, $headers);
    }


}
