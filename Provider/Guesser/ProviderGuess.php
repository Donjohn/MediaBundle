<?php
/**
 * @author Donjohn
 * @date 25/08/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Provider\Guesser;

use Symfony\Component\Form\Guess\Guess;

/**
 * Class ProviderGuess.
 */
class ProviderGuess extends Guess
{
    /** @var string $providerAlias */
    protected $providerAlias;

    /**
     * ProviderGuess constructor.
     *
     * @param string $providerAlias
     * @param int    $confidence
     */
    public function __construct(string $providerAlias, int $confidence)
    {
        $this->providerAlias = $providerAlias;
        parent::__construct($confidence);
    }

    /**
     * @return string
     */
    public function getProviderAlias(): string
    {
        return $this->providerAlias;
    }
}
