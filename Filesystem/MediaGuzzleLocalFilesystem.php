<?php
/**
 * @author jgn
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
    protected function createFilesystem()
    {
        return new Filesystem(new Adapter\Local($this->rootFolder, false, '0775'));
    }

    public function hasMedia(Media $media)
    {
        return $this->filesystem->has($this->getPath($media));
    }

    public function removeMedia(Media $media)
    {
        if ($this->hasMedia($media)) return $this->filesystem->delete($this->getPath($media));
        return true;
    }

    public function createMedia(Media $media, File $file)
    {
        return $this->filesystem->write($this->getPath($media), file_get_contents($file->getRealPath())) && $this->filesystem->delete($file->getRealPath());

    }


}
