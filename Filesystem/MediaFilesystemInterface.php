<?php
/**
 * @author Donjohn
 * @date 22/03/2018
 * @description For ...
 */

namespace Donjohn\MediaBundle\Filesystem;

use Donjohn\MediaBundle\Model\Media;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Interface MediaFilesystemInterface.
 */
interface MediaFilesystemInterface
{
    /**
     * @param Media $media
     *
     * @return string full folder path
     */
    public function getFullPath(Media $media): string;

    /**
     * @param Media $media
     *
     * @return string return full media url
     */
    public function getWebPath(Media $media): string;

    /**
     * @param Media $media
     *
     * @return string media path from public|web folder
     */
    public function getPath(Media $media): string;

    /**
     * check if file is present.
     *
     * @param Media $media
     *
     * @return bool
     */
    public function hasMedia(Media $media): bool;

    /**
     * @param Media $media
     *
     * @return bool
     */
    public function removeMedia(Media $media): bool;

    /**
     * write file for the media.
     *
     * @param Media $media
     * @param File  $file
     *
     * @return bool
     */
    public function createMedia(Media $media, File $file): bool;

    /**
     * @return Filesystem
     */
    public function createOrGetFilesystem(): Filesystem;
}
