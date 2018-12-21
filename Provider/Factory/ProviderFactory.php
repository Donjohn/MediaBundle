<?php

namespace Donjohn\MediaBundle\Provider\Factory;

use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\NotFoundProviderException;
use Donjohn\MediaBundle\Provider\Guesser\ProviderGuess;
use Donjohn\MediaBundle\Provider\ProviderInterface;
use RuntimeException;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\HttpFoundation\File\File;

/**
 * description.
 *
 * @author Donjohn
 */
class ProviderFactory
{
    /**
     * @var array
     */
    protected $providers = array();

    /**
     * @param ProviderInterface $provider
     */
    public function addProvider(ProviderInterface $provider): void
    {
        $this->providers[$provider->getAlias()] = $provider;
    }

    /**
     * get Media.
     *
     * @param string|Media $mixed or Media
     *
     * @return ProviderInterface $provider
     *
     * @throws RuntimeException
     */
    public function getProvider($mixed): ProviderInterface
    {
        $alias = $mixed instanceof Media ? $mixed->getProviderName() : $mixed;

        if (array_key_exists($alias, $this->providers)) {
            return $this->providers[$alias];
        }

        throw new NotFoundProviderException('no provider "'.$alias.'" found or enabled');
    }

    /**
     * @return array providers
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @param File $file
     *
     * @return ProviderGuess|Guess
     *
     * @throws NotFoundProviderException
     */
    public function guessProvider(File $file = null): Guess
    {
        $guesses = array();

        /** @var ProviderInterface $provider */
        foreach ($this->providers as $provider) {
            $guesses[] = $provider->guess($file);
        }
        $result = ProviderGuess::getBestGuess($guesses);
        if (null === $result) {
            throw new NotFoundProviderException('could not guess a provider for file '.$file->getFilename());
        }

        return $result;
    }
}
