<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Filesystem\MediaFilesystemInterface;
use Donjohn\MediaBundle\Model\Media;
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
    protected $twig;


    /**
     * @var MediaFilesystemInterface $mediaFilesystem
     */
    protected $mediaFilesystem;

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

    public function setMediaFilesystem(MediaFilesystemInterface $filesystem)
    {
        $this->mediaFilesystem = $filesystem;
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
    final public function setTwig(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param Media $media
     * @param null $filter
     * @param array $options
     * @return string
     */
    public function render(Media $media, $filter = 'reference', array $options = array()){
        return $this->twig->render($this->getTemplate(),
                            array('mediaWebPath' => $this->mediaFilesystem->getWebPath($media),
                                'options' => $options)
                            );
    }

    /**
     * {@inheritdoc}
     */
    final public function validateMimeType($type)
    {
        if (count($this->getAllowedTypes()) && !preg_match('#'.implode('|',$this->getAllowedTypes()).'#', $type)) throw new InvalidMimeTypeException(sprintf('%s is not supported', $type));

        return true;
    }

    /**
     * @inheritdoc
     * @param null|File $file
     */
    final public function guess($file = null){

        $guesses = [];
        if ($file instanceof File && count($this->getAllowedTypes())) {
            if (preg_match('#'.implode('|',$this->getAllowedTypes()).'#', $file->getMimeType())) {
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
