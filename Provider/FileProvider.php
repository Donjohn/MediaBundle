<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Filesystem\MediaFilesystemInterface;
use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * description.
 *
 * @author Donjohn
 */
class FileProvider extends BaseProvider
{
    /** @var string $fileMaxSize */
    private $fileMaxSize;

    /**
     * FileProvider constructor.
     *
     * @param MediaFilesystemInterface $filesystem
     * @param string                   $fileMaxSize
     */
    public function __construct(MediaFilesystemInterface $filesystem, string $fileMaxSize)
    {
        $this->mediaFilesystem = $filesystem;
        $this->fileMaxSize = $fileMaxSize;
    }

    /**
     * @return float|int|string
     */
    public function getFileMaxSize()
    {
        $number = substr($this->fileMaxSize, 0, -1);
        switch (strtoupper(substr($this->fileMaxSize, -1))) {
            case 'K':
                return $this->fileMaxSize = $number * 1024;
            case 'M':
                return $this->fileMaxSize = $number * (1024 ** 2);
            case 'G':
                return $this->fileMaxSize = $number * (1024 ** 3);
            case 'T':
                return $this->fileMaxSize = $number * (1024 ** 4);
            case 'P':
                return $this->fileMaxSize = $number * (1024 ** 5);
            default:
                return $this->fileMaxSize;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'file';
    }

    /**
     * @return array
     */
    public function getAllowedTypes(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return '@DonjohnMedia/Provider/media.'.$this->getAlias().'.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function postLoad(Media $media): void
    {
        //nada
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(Media $media): void
    {
        $fileName = null;
        if ($media->getBinaryContent() instanceof UploadedFile) {
            $fileName = $media->getBinaryContent()->getClientOriginalName();
        } elseif ($media->getBinaryContent() instanceof File) {
            $fileName = $media->getBinaryContent()->getBasename();
        }

        if (null === $fileName) {
            throw new InvalidMimeTypeException('invalid media');
        }
        if (null !== $media->getBinaryContent()) {
            $media->setFilename(sha1($media->getName().random_int(11111, 99999)).'.'.pathinfo($fileName, PATHINFO_EXTENSION));

            if (0 === stripos(PHP_OS, 'WIN')) {
                $media->setMd5(md5_file($media->getBinaryContent()->getRealPath()));
            } else {
                $output = shell_exec('md5sum -b '.escapeshellarg($media->getBinaryContent()->getRealPath()));
                $media->setMd5(substr($output, 0, strpos($output, ' ') + 1));
            }
        }

        $mimeType = $media->getBinaryContent()->getMimeType();
        $this->validateMimeType($mimeType);
        $this->extractMetaData($media);

        $media->setMimeType($mimeType);
        $media->setProviderName($this->getAlias());
        $media->setName($media->getName() ?: $fileName); //to keep oldname
        $media->setOriginalFilename($media->getOriginalFilename() ?: $fileName); //to keep originel fielname
        $media->addMetadata('filename', $fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(Media $media): void
    {
        if ($media->getBinaryContent() instanceof File) {
            $this->mediaFilesystem->createMedia($media, $media->getBinaryContent());
            $media->setBinaryContent(null);
        }
        $this->postLoad($media);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(Media $media): void
    {
        if (null !== $media->getBinaryContent()) {
            $media->setFilename(sha1($media->getName().random_int(11111, 99999)).'.'.pathinfo($media->getOriginalFilename(), PATHINFO_EXTENSION));

            if (0 === stripos(PHP_OS, 'WIN')) {
                $media->setMd5(md5_file($media->getBinaryContent()->getRealPath()));
            } else {
                $output = shell_exec('md5sum -b '.escapeshellarg($media->getBinaryContent()->getRealPath()));
                $media->setMd5(substr($output, 0, strpos($output, ' ') + 1));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(Media $media): void
    {
        $this->postPersist($media);
        $oldMedia = $media->oldMedia();
        if ($oldMedia instanceof Media) {
            $this->preRemove($oldMedia);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove(Media $media): void
    {
        $this->mediaFilesystem->removeMedia($media);
    }

    /**
     * {@inheritdoc}
     */
    public function extractMetaData(Media $media): void
    {
        //nada
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function addProviderOptions(array $options): array
    {
        $options['constraints'] = array_merge(
            $options['constraints'] ?? [],
            [new Constraints\File(['maxSize' => $this->getFileMaxSize()])]
        );

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function addEditForm(FormInterface $form, array $options): void
    {
        $form->add('binaryContent', FileType::class, $this->addProviderOptions($options));
    }

    /**
     * {@inheritdoc}
     */
    public function addCreateForm(FormInterface $form, array $options): void
    {
        $form->add('binaryContent', FileType::class, $this->addProviderOptions($options));
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadResponse(Media $media, array $headers = array()): Response
    {
        // build the default headers
        $headers = array_merge(array(
            'Content-Type' => $media->getMimeType(),
            'Content-Disposition' => sprintf('attachment; filename="%s"', $media->getName()),
        ), $headers);

        return new BinaryFileResponse($this->mediaFilesystem->getFullPath($media), 200, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(Media $media, string $filter = null, bool $fullPath = false): string
    {
        return $fullPath ?
            $this->mediaFilesystem->getWebPath($media) :
            $this->mediaFilesystem->getPath($media);
    }
}
