<?php
/**
 * @author Donjohn
 * @date 21/03/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Filesystem;


use Donjohn\MediaBundle\Model\Media;
use Gaufrette\Adapter;
use Gaufrette\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class MediaGuzzleLocalFilesystem extends MediaLocalFilesystem
{

    /**
     * @var Filesystem $filesystem
     */
    protected $filesystem;

    /**
     * @return Filesystem
     */
    public function createOrGetFilesystem()
    {
        $this->filesystem = $this->filesystem ?: new Filesystem(new Adapter\Local($this->rootFolder, false, '0775'));
        return $this->filesystem;
    }

    public function hasMedia(Media $media)
    {
        return $this->createOrGetFilesystem()->has($this->getPath($media));
    }

    public function removeMedia(Media $media)
    {
        if ($this->hasMedia($media)) return $this->createOrGetFilesystem()->delete($this->getPath($media));
        return true;
    }

    public function createMedia(Media $media, File $file)
    {
        return $this->createOrGetFilesystem()->write($this->getPath($media), file_get_contents($file->getRealPath())) && unlink($file->getRealPath());

    }


}
