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
    /** @var string $providerAlias  */
    protected $providerAlias;

    /**
     * ProviderGuess constructor.
     * @param string $providerAlias
     * @param $confidence
     */
    public function __construct($providerAlias, $confidence)
    {
        $this->providerAlias = $providerAlias;
        parent::__construct($confidence);
    }

    /**
     * @return string
     */
    public function getProviderAlias()
    {
        return $this->providerAlias;
    }

}
