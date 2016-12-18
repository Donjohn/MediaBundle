<?php

namespace Donjohn\MediaBundle\Provider\Factory;

use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\NotFoundProviderException;
use Donjohn\MediaBundle\Provider\ProviderInterface;
use RuntimeException;

/**
 * description 
 * @author Donjohn
 */
class ProviderFactory {

    protected $providers = array();
    protected $filesystemMap;
    protected $filesystem;

    /**
     * 
     * @param ProviderInterface $provider
     * @param string $alias
     */
    public function addProvider(ProviderInterface $provider, $alias) {
        $provider->setAlias($alias);
        $this->providers[$alias] = $provider;
    }

    /**
     * get Media
     * @param string|Media $mixed or Media
     * @return ProviderInterface $provider
     * @throws RuntimeException
     */
    public function getProvider($mixed) {

        $alias = $mixed instanceof Media ? $mixed->getProviderName() : $mixed;
        
        if (array_key_exists($alias, $this->providers)) {
            return $this->providers[$alias];
        }

        throw new NotFoundProviderException('no provider defined for media ' . $alias);
    }

}
