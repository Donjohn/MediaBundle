<?php
/**
 * @author Donjohn
 * @date 21/03/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Filesystem;

use Donjohn\MediaBundle\Model\Media;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class MediaLocalFilesystem.
 */
class MediaLocalFilesystem implements MediaFilesystemInterface
{
    /**
     * @var string
     */
    protected $uploadFolder;

    /**
     * @var string
     */
    protected $rootFolder;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * MediaLocalFilesystem constructor.
     *
     * @param RequestStack $requestStack
     * @param string       $rootFolder
     * @param string       $uploadFolder
     */
    public function __construct(RequestStack $requestStack, string $rootFolder, string $uploadFolder)
    {
        $this->requestStack = $requestStack;
        $this->rootFolder = $rootFolder;
        $this->uploadFolder = $uploadFolder;
    }

    /**
     * @return Filesystem
     */
    public function createOrGetFilesystem(): Filesystem
    {
        $this->filesystem = $this->filesystem ?: new Filesystem();

        return $this->filesystem;
    }

    /**
     * @param Media $media
     *
     * @return string
     */
    public function getWebPath(Media $media): string
    {
        return sprintf('%s%s',
                        $this->requestStack->getCurrentRequest()->getBaseUrl(),
                        $this->getPath($media)
                    );
    }

    /**
     * @param Media $media
     *
     * @return string
     */
    public function getFullPath(Media $media): string
    {
        return sprintf('%s%s',
            $this->rootFolder,
            $this->getPath($media)
            );
    }

    /**
     * @param Media $media
     *
     * @return string
     */
    public function getPath(Media $media): string
    {
        if (null === $media->getId()) {
            throw new \RuntimeException('media must be psersisted before calling getPath');
        }
        $firstLevel = 100000;
        $secondLevel = 1000;

        $rep_first_level = (int) ($media->getId() / $firstLevel);
        $rep_second_level = (int) (($media->getId() - ($rep_first_level * $firstLevel)) / $secondLevel);

        return sprintf('%s/%04s/%02s/%s', $this->uploadFolder, $rep_first_level + 1, $rep_second_level + 1, $media->getFilename());
    }

    /**
     * @param Media $media
     *
     * @return bool
     */
    public function hasMedia(Media $media): bool
    {
        return $this->createOrGetFilesystem()->exists($this->getFullPath($media));
    }

    /**
     * @param Media $media
     *
     * @return bool|void
     */
    public function removeMedia(Media $media): bool
    {
        if ($this->hasMedia($media)) {
            $this->createOrGetFilesystem()->remove($this->getFullPath($media));
        }

        return true;
    }

    /**
     * @param Media $media
     * @param File  $file
     *
     * @return bool|void
     */
    public function createMedia(Media $media, File $file): bool
    {
        $this->createOrGetFilesystem()->copy($file->getRealPath(), $this->getFullPath($media));
        $this->createOrGetFilesystem()->remove($file->getRealPath());

        return true;
    }
}
