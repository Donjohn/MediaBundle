<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Model\Media;
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

    public function extractMetaData(Media $oMedia)
    {
//
//        try {
//            $this->adapter = new Imagine();
//            $this->adapter->open($this->filesystem->get($oMedia)->getKey());
//        } catch (\Exception $e) {
//            //throw new \RuntimeException($e->getMessage());
//        }
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

    public function getPath(Media $oMedia, $filter= null)
    {
        $path = parent::getPath($oMedia);
        return  ($filter && $filter!='reference') ? $this->cacheManager->getBrowserPath($path, $filter) : $path;
    }
    
}
