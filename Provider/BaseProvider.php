<?php

declare(strict_types=1);

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
    final public function setMediaFilesystem(MediaFilesystemInterface $filesystem): ProviderInterface
    {
        $this->mediaFilesystem = $filesystem;

        return $this;
    }

    /**
     * @return MediaFilesystemInterface
     */
    final public function getMediaFilesystem(): MediaFilesystemInterface
    {
        return $this->mediaFilesystem;
    }

    /**
     * @return array
     */
    abstract public function getAllowedTypes(): array;

    /**
     * @return string
     */
    abstract public function getTemplate(): string;

    /**
     * @return string
     */
    abstract public function getAlias(): string;

    /**
     * {@inheritdoc}
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
     * @return \Twig_Environment
     */
    final public function getTwig(): \Twig_Environment
    {
        return $this->twig;
    }

    /**
     * {@inheritdoc}
     */
    public function render(Media $media, string $filter = null, array $options = array()): string
    {
        return $this->getTwig()->render($this->getTemplate(),
                            array('mediaPath' => $this->mediaFilesystem->getPath($media),
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
