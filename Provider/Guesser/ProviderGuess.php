<?php
/**
 * @author jgn
 * @date 25/08/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Provider\Guesser;


use Symfony\Component\Form\Guess\Guess;

class ProviderGuess extends Guess
{

    protected $providerAlias;

    public function __construct($providerAlias, $confidence)
    {
        $this->providerAlias = $providerAlias;
        parent::__construct($confidence);
    }

    public function getProviderAlias()
    {
        return $this->providerAlias;
    }

    /**
     * Returns the guess most likely to be correct from a list of guesses.
     *
     * If there are multiple guesses with the same, highest confidence, the
     * returned guess is any of them.
     *
     * @param ProviderGuess[] $guesses An array of guesses
     *
     * @return Guess|ProviderGuess|null
     */
    public static function getBestGuess(array $guesses)
    {
        return parent::getBestGuess($guesses);
    }

}