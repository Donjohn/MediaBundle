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
        return $filter ?
                $this->cacheManager->getBrowserPath(parent::getPath($media), $filter) :
                parent::getWebPath($media);
    }


    public function getFullPath(Media $media, $filter = null)
    {

        return $filter ?
                sprintf('%s%s',
                    $this->rootFolder,
                    str_replace($this->requestStack->getCurrentRequest()->getSchemeAndHttpHost(), '', $this->cacheManager->resolve(parent::getPath($media), $filter) )
                ) :
                parent::getFullPath($media);
    }

    public function getPath(Media $media, $filter = null)
    {
        $path = parent::getPath($media);

        return $filter ?
                str_replace($this->requestStack->getCurrentRequest()->getSchemeAndHttpHost(), '', $this->cacheManager->getBrowserPath($path, $filter) ):
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
            $this->cacheManager->remove($this->getPath($media));
            return parent::removeMedia($media);
        }

        return true;
    }


}
