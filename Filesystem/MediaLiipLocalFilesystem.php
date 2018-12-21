<?php
/**
 * @author Donjohn
 * @date 21/03/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Filesystem;

use Donjohn\MediaBundle\Model\Media;
use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareTrait;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class MediaLiipLocalFilesystem.
 */
class MediaLiipLocalFilesystem implements MediaFilesystemInterface
{
    /** @var MediaFilesystemInterface $mediaLocalFilesystem */
    protected $mediaLocalFilesystem;

    use CacheManagerAwareTrait;

    /**
     * @var string
     */
    protected $rootFolder;

    /**
     * MediaLiipLocalFilesystem constructor.
     *
     * @param MediaFilesystemInterface $mediaLocalFilesystem
     * @param CacheManager             $cacheManager
     * @param string                   $rootFolder
     */
    public function __construct(MediaFilesystemInterface $mediaLocalFilesystem, CacheManager $cacheManager, string $rootFolder)
    {
        $this->mediaLocalFilesystem = $mediaLocalFilesystem;
        $this->rootFolder = $rootFolder;
        $this->setCacheManager($cacheManager);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl(): string
    {
        return $this->mediaLocalFilesystem->getBaseUrl();
    }

    /**
     * @param Media $media
     * @param File  $file
     *
     * @return bool
     */
    public function createMedia(Media $media, File $file): bool
    {
        return $this->mediaLocalFilesystem->createMedia($media, $file);
    }

    /**
     * @return Filesystem
     */
    public function createOrGetFilesystem(): Filesystem
    {
        return $this->mediaLocalFilesystem->createOrGetFilesystem();
    }

    /**
     * @param Media       $media
     * @param string|null $filter
     *
     * @return string
     */
    public function getWebPath(Media $media, string $filter = null): string
    {
        return null !== $filter ?
                $this->cacheManager->getBrowserPath($this->mediaLocalFilesystem->getPath($media), $filter) :
                $this->mediaLocalFilesystem->getWebPath($media);
    }

    /**
     * @param Media  $media
     * @param string $filter
     *
     * @return string
     */
    public function getFullPath(Media $media, string $filter = null): string
    {
        return $filter ?
                sprintf('%s%s',
                    $this->rootFolder,
                    str_replace($this->getBaseUrl(), '', $this->getWebPath($media, $filter))
                ) :
                $this->mediaLocalFilesystem->getFullPath($media);
    }

    /**
     * @param Media  $media
     * @param string $filter
     *
     * @return mixed|string
     */
    public function getPath(Media $media, string $filter = null): string
    {
        $path = $this->mediaLocalFilesystem->getPath($media);

        return $filter ?
                str_replace($this->getBaseUrl(), '', $this->cacheManager->getBrowserPath($path, $filter)) :
                $path;
    }

    /**
     * @param Media  $media
     * @param string $filter
     *
     * @return bool
     */
    public function hasMedia(Media $media, string $filter = null): bool
    {
        return $filter ?
                $this->cacheManager->isStored($this->getPath($media), $filter) :
                $this->mediaLocalFilesystem->hasMedia($media);
    }

    /**
     * @param Media $media
     *
     * @return bool
     */
    public function removeMedia(Media $media): bool
    {
        if ($this->mediaLocalFilesystem->hasMedia($media)) {
            $this->cacheManager->remove($this->getPath($media));
            $this->mediaLocalFilesystem->removeMedia($media);
        }

        return true;
    }
}
