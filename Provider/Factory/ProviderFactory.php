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

    /**
     * @var array $providers
     */
    protected $providers = array();
    /**
     * @var  array $templates
     */
    protected $templates=array();

    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * 
     * @param ProviderInterface $provider
     * @param string $alias
     */
    public function addProvider(ProviderInterface $provider) {
        $this->providers[$provider->getAlias()] = $provider;

        $provider->setTemplate($this->getTemplate($provider->getAlias()));
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


    private function getTemplate($providerAlias){

        return isset($this->templates[$providerAlias]) ? $this->templates[$providerAlias] : false;

    }

}
