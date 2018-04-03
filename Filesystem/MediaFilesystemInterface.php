<?php
/**
 * @author Donjohn
 * @date 22/03/2018
 * @description For ...
 */

namespace Donjohn\MediaBundle\Filesystem;


use Donjohn\MediaBundle\Model\Media;
use Symfony\Component\HttpFoundation\File\File;

interface MediaFilesystemInterface
{
    /**
     * @param Media $media
     * @param null $filter
     * @return string full folder path
     */
    public function getFullPath(Media $media);

    /**
     * @param Media $media
     * @param null $filter
     * @return string return full media url
     */
    public function getWebPath(Media $media);

    /**
     * @param Media $media
     * @param null $filter
     * @return string media path from public|web folder
     */
    public function getPath(Media $media);

    /**
     * check if file is present
     * @param Media $media
     * @param null|string $filter
     * @return bool
     */
    public function hasMedia(Media $media);

    /**
     * @param Media $media
     * @param null|string $filter
     * @return bool
     */
    public function removeMedia(Media $media);

    /**
     * write file for the media
     * @param Media $media
     * @param File $file
     * @return bool
     */
    public function createMedia(Media $media, File $file);

    /*
     * @return filesystem
     */
    public function createOrGetFilesystem();


}
