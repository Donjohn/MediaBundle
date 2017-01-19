<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Model\Media;
use Gaufrette\Exception\FileNotFound;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

/**
 * description 
 * @author Donjohn
 */
class ImageProvider extends FileProvider  {
    
    public $allowedTypes=array('image/jpeg', 'image/png');
    /**
     * @var CacheManager $cacheManager
     */
    protected $cacheManager;
    /**
     * @var FilterConfiguration $filterConfiguration
     */
    protected $filterConfiguration;

    public function __construct($rootFolder, $uploadFolder, CacheManager $cacheManager, FilterConfiguration $filterConfiguration)
    {
        $this->cacheManager = $cacheManager;
        $this->filterConfiguration = $filterConfiguration;
        parent::__construct($rootFolder, $uploadFolder);
    }

    protected function delete(Media $oMedia, $filter=null)
    {
        try {
            return $this->filesystem->delete($this->getPath($oMedia, $filter));
        } catch (FileNotFound $e) {
            //do nothing, file already deleted
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function postLoad(Media $oMedia)
    {
        $paths = array('reference' => $this->getPath($oMedia));
        foreach ($this->filterConfiguration->all() as $filter=> $configuration) $paths[$filter] = $this->getPath($oMedia, $filter);
        $oMedia->setPaths($paths);
    }

    /**
     * @inheritdoc
     */
    public function preRemove(Media $oMedia)
    {
        $this->delete($oMedia);
        foreach ($this->filterConfiguration->all() as $filter=> $configuration) $this->delete($oMedia, $filter);

        return true;
    }

    public function getPath(Media $oMedia, $filter= null)
    {
        $path = parent::getPath($oMedia);
        return  ($filter && $filter!='reference') ? $this->cacheManager->getBrowserPath($path, $filter) : $path;
    }
    
}
