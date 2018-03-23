<?php
/**
 * @author jgn
 * @date 22/03/2018
 * @description For ...
 */

namespace Donjohn\MediaBundle\Filesystem;


use Donjohn\MediaBundle\Model\Media;

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

}