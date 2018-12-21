<?php
/**
 * @author Donjohn
 * @date 07/03/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Twig\Extension;

use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Donjohn\MediaBundle\Twig\TokenParser\MediaPathTokenParser;
use Donjohn\MediaBundle\Twig\TokenParser\MediaTokenParser;

/**
 * Class MediaExtension.
 */
class MediaExtension extends \Twig_Extension
{
    /**
     * @var ProviderFactory
     */
    protected $providerFactory;

    /**
     * MediaExtension constructor.
     *
     * @param ProviderFactory $providerFactory
     */
    public function __construct(ProviderFactory $providerFactory)
    {
        $this->providerFactory = $providerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers(): array
    {
        return array(
            new MediaTokenParser(self::class),
            new MediaPathTokenParser(self::class),
        );
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new \Twig_Filter('mediaPath', array($this, 'media_path'), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'donjohn_media';
    }

    /**
     * @param Media|null $media
     * @param $filter
     * @param $attributes
     *
     * @return string
     */
    public function media(Media $media = null, string $filter = null, array $attributes = array()): string
    {
        if (null !== $media) {
            $provider = $this->providerFactory->getProvider($media);

            return $provider->render($media, $filter, $attributes);
        }

        return '';
    }

    /**
     * @param Media|null $media
     * @param string     $filter
     * @param bool       $fullPath
     *
     * @return string
     */
    public function media_path(Media $media = null, string $filter = null, bool $fullPath = false): string
    {
        if (null !== $media) {
            $provider = $this->providerFactory->getProvider($media);

            return $provider->getPath($media, $filter, $fullPath);
        }

        return '';
    }
}
