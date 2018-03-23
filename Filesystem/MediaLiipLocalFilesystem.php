<?php
/**
 * @author jgn
 * @date 21/03/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Filesystem;


use Donjohn\MediaBundle\Model\Media;
use Liip\ImagineBundle\Imagine\Cache\CacheManagerAwareTrait;

class MediaLiipLocalFilesystem extends MediaLocalFilesystem
{

    use CacheManagerAwareTrait;

    /**
     * @return string
     */
    public function getWebPath(Media $media, $filter = null)
    {
        return sprintf('%s%s',
            $this->request->getSchemeAndHttpHost(),
            $this->getPath($media, $filter)
            );
    }

    public function getFullPath(Media $media, $filter = null)
    {
        return sprintf('%s%s',
            $this->rootFolder,
            $this->getPath($media, $filter)
            );
    }

    public function getPath(Media $media, $filter=null)
    {
        $path = parent::getPath($media);

        return $filter ?
                str_replace($this->request->getSchemeAndHttpHost(), '', $this->cacheManager->getBrowserPath($path, $filter)) :
                $path ;
    }

    public function hasMedia(Media $media, $filter = null)
    {
        return $filter ?
                $this->cacheManager->isStored($this->getPath($media), $filter) :
                parent::hasMedia($media);
    }

    public function removeMedia(Media $media)
    {
        if (parent::hasMedia($media)) {
            return $this->cacheManager->remove($this->getPath($media)) && parent::removeMedia($media);
        }

        return true;
    }


}
