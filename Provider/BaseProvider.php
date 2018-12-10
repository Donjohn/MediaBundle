<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Filesystem\MediaFilesystemInterface;
use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Exception\InvalidMimeTypeException;
use Donjohn\MediaBundle\Provider\Guesser\ProviderGuess;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;

/**
 * description.
 *
 * @author Donjohn
 */
abstract class BaseProvider implements ProviderInterface
{
    /**
     * @var string|null
     */
    private $template;

    /**
     * @var array|null
     */
    private $allowedTypes;

    /**
     * @var \Twig_Environment|null
     */
    protected $twig;

    /**
     * @var MediaFilesystemInterface
     */
    protected $mediaFilesystem;

    /**
     * {@inheritdoc}
     */
    final public function setTemplate(string $template): ProviderInterface
    {
        if (empty($template)) {
            throw new \InvalidArgumentException('please configure a template name for '.$this->getAlias().' provider');
        }
        $this->template = $template;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function setAllowedTypes(array $allowedTypes): ProviderInterface
    {
        $this->allowedTypes = $allowedTypes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function setMediaFilesystem(MediaFilesystemInterface $filesystem): ProviderInterface
    {
        $this->mediaFilesystem = $filesystem;

        return $this;
    }

    /**
     * @return array
     */
    final protected function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }

    /**
     * @return string
     */
    final protected function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return string
     */
    abstract public function getAlias(): string;

    /**
     * @inheritDoc
     */
    abstract public function getDownloadResponse(Media $media, array $headers = array()): Response;


    /**
     * @param \Twig_Environment $twig
     *
     * @return ProviderInterface
     */
    final public function setTwig(\Twig_Environment $twig): ProviderInterface
    {
        $this->twig = $twig;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render(Media $media, string $filter = null, array $options = array()): string
    {
        return $this->twig->render($this->getTemplate(),
                            array('mediaWebPath' => $this->mediaFilesystem->getWebPath($media),
                                'name' => $media->getName(),
                                'options' => $options, )
                            );
    }

    /**
     * {@inheritdoc}
     */
    final public function validateMimeType(string $type): bool
    {
        if (count($this->getAllowedTypes()) && !preg_match('#'.implode('|', $this->getAllowedTypes()).'#', $type)) {
            throw new InvalidMimeTypeException(sprintf('%s is not supported', $type));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param null|File $file
     */
    final public function guess(File $file = null): Guess
    {
        $guesses = [];
        if ($file instanceof File && count($this->getAllowedTypes())) {
            if (preg_match('#'.implode('|', $this->getAllowedTypes()).'#', $file->getMimeType())) {
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
