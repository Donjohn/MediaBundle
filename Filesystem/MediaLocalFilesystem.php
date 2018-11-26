<?php
/**
 * @author Donjohn
 * @date 21/03/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Filesystem;


use Donjohn\MediaBundle\Model\Media;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;

class MediaLocalFilesystem implements MediaFilesystemInterface
{

    /**
     * @var string $uploadFolder
     */
    protected $uploadFolder;


    /**
     * @var string $rootFolder
     */
    protected $rootFolder;

    /**
     * @var RequestStack $requestStack
     */
    protected $requestStack;

    /**
     * @var Filesystem $filesystem
     */
    protected $filesystem;

    /**
     * MediaLocalFilesystem constructor.
     * @param string $webFolder
     * @param string $uploadFolder
     */
    public function __construct(RequestStack $requestStack, $rootFolder, $uploadFolder)
    {
        $this->requestStack = $requestStack;
        $this->rootFolder = $rootFolder;
        $this->uploadFolder = $uploadFolder;
    }

    /**
     * @return Filesystem
     */
    public function createOrGetFilesystem()
    {
        $this->filesystem = $this->filesystem ?: new Filesystem();
        return $this->filesystem;
    }

    /**
     * @return string
     */
    public function getWebPath(Media $media)
    {
        return sprintf('%s%s',
                        $this->requestStack->getCurrentRequest()->getBaseUrl(),
                        $this->getPath($media)
                    );
    }



    public function getFullPath(Media $media)
    {
        return sprintf('%s%s',
            $this->rootFolder,
            $this->getPath($media)
            );
    }


    public function getPath(Media $media)
    {
        if ($media->getId() === null ) throw new \RuntimeException('media must be psersisted before calling getPath');
        $firstLevel=100000;
        $secondLevel=1000;

        $rep_first_level = (int) ($media->getId() / $firstLevel);
        $rep_second_level = (int) (($media->getId() - ($rep_first_level * $firstLevel)) / $secondLevel);

        return sprintf('%s/%04s/%02s/%s', $this->uploadFolder,  $rep_first_level + 1, $rep_second_level + 1, $media->getFilename() );
    }

    public function hasMedia(Media $media)
    {
        return $this->createOrGetFilesystem()->exists($this->getFullPath($media));
    }

    public function removeMedia(Media $media)
    {
        if ($this->hasMedia($media)) $this->createOrGetFilesystem()->remove($this->getFullPath($media));
    }

    public function createMedia(Media $media, File $file)
    {
        $this->createOrGetFilesystem()->copy($file->getRealPath(), $this->getFullPath($media));
        $this->createOrGetFilesystem()->remove($file->getRealPath());

    }


}
