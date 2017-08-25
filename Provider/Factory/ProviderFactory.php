<?php

namespace Donjohn\MediaBundle\Provider\Factory;

use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\NotFoundProviderException;
use Donjohn\MediaBundle\Provider\Guesser\ProviderGuess;
use Donjohn\MediaBundle\Provider\ProviderInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * description 
 * @author Donjohn
 */
class ProviderFactory {

    /**
     * @var array $providers
     */
    protected $providers = array();
    /** @var array $allowedTypes */
    protected $allowedTypes = array();
    /** @var array $enables */
    protected $enables = array();
    /**
     * @var  array $templates
     */
    protected $templates=array();

    public function __construct(array $config)
    {
        $this->templates = array_map(function($item) {return $item['template'];}, $config);
        $this->allowedTypes = array_map(function($item) {return $item['allowed_types'];}, $config);;
        $this->enables = array_map(function($item) {return $item['enabled'];}, $config);;
    }

    /**
     * 
     * @param ProviderInterface $provider
     * @param string $alias
     */
    public function addProvider(ProviderInterface $provider) {
        if ($this->enables[$provider->getAlias()]) {
            $this->providers[$provider->getAlias()] = $provider;

            $provider->setTemplate($this->getTemplateProvider($provider->getAlias()));
            $provider->setAllowedTypes($this->getAllowedTypesProvider($provider->getAlias()));
        }
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

        throw new NotFoundProviderException('no provider "' . $alias . '" defined');
    }

    /**
     * @return array providers
     */
    public function getProviders() {
        return $this->providers;
    }

    /**
     * @param File $file
     * @return null|ProviderGuess
     */
    public function guessProvider($file = null)
    {
        $guesses = array();

        /** @var ProviderInterface $provider */
        foreach ($this->providers as $provider) {
            $guesses[] = $provider->guess($file);
        }

        return ProviderGuess::getBestGuess($guesses);
    }


    private function getTemplateProvider($providerAlias){

        return isset($this->templates[$providerAlias]) ? $this->templates[$providerAlias] : false;

    }


    private function getAllowedTypesProvider($providerAlias){

        return isset($this->allowedTypes[$providerAlias]) ? $this->allowedTypes[$providerAlias] : false;

    }

}
