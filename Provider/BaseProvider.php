<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Model\MediaInterface;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Donjohn\MediaBundle\Provider\Guesser\ProviderGuess;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\HttpFoundation\File\File;


/**
 * description 
 * @author Donjohn
 */
abstract class BaseProvider implements ProviderInterface {

    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $allowedTypes;

    /**
     * @var \Twig_Environment $twig
     */
    private $twig;

    /**
     * @param $template
     * @return $this
     */
    final public function setTemplate($template)
    {
        if (empty($template)) throw new \InvalidArgumentException('please configure a template name for '.$this->getAlias().' provider');
        $this->template = $template;
        return $this;
    }

    /**
     * @param array $allowedTypes
     * @return $this
     */
    final public function setAllowedTypes(array $allowedTypes)
    {
        $this->allowedTypes = $allowedTypes;
        return $this;
    }

    /**
     * @return array
     */
    final protected function getAllowedTypes()
    {
        return $this->allowedTypes;
    }

    /**
     * @return string
     */
    final protected function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return string
     */
    abstract public function getAlias();


    /**
     * @param \Twig_Environment $twig
     */
    public function setTwig(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param MediaInterface $media
     * @param null $filter
     * @param array $options
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function render(MediaInterface $media, $filter = null, array $options = array()){
        return $this->twig->render($this->getTemplate(),
                            array('media' => $media,
                                'filter' => $filter,
                                'options' => $options)
                            );
    }

    /**
     * {@inheritdoc}
     */
    final public function validateMimeType($type)
    {
        if (count($this->allowedTypes) && !preg_match('#'.implode('|',$this->allowedTypes).'#', $type)) throw new InvalidMimeTypeException(sprintf('%s is not supported', $type));

        return true;
    }

    /**
     * @inheritdoc
     * @param null|File $file
     */
    final public function guess($file = null){

        $guesses = [];
        if ($file instanceof File && count($this->allowedTypes)) {
            if (preg_match('#'.implode('|',$this->allowedTypes).'#', $file->getMimeType())) {
                $guesses[] = new ProviderGuess($this->getAlias(), Guess::HIGH_CONFIDENCE);
            } else {
                $guesses[] = new ProviderGuess($this->getAlias(), Guess::LOW_CONFIDENCE);
            }
        } else {
            $guesses[] = new ProviderGuess($this->getAlias(), Guess::MEDIUM_CONFIDENCE);
        }

        return ProviderGuess::getBestGuess($guesses);

    }


}
