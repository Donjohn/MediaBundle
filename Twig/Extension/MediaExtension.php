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
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class MediaExtension.
 */
class MediaExtension extends \Twig_Extension
{
    /**
     * @var ProviderFactory
     */
    protected $providerFactory;

    /** @var ObjectNormalizer $serializer */
    protected $normalizer;

    /**
     * MediaExtension constructor.
     *
     * @param ProviderFactory  $providerFactory
     * @param ObjectNormalizer $normalizer
     */
    public function __construct(ProviderFactory $providerFactory, ObjectNormalizer $normalizer)
    {
        $this->providerFactory = $providerFactory;
        $this->normalizer = $normalizer;
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
            new \Twig_Filter('mediaPath', array($this, 'mediaPath'), ['is_safe' => ['html']]),
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
     * @param Media|array|null $media
     * @param $filter
     * @param $attributes
     *
     * @return string
     */
    public function media($media = null, string $filter = null, array $attributes = array()): string
    {
        $media = $this->denormalize($media);
        if (null !== $media) {
            $provider = $this->providerFactory->getProvider($media);

            return $provider->render($media, $filter, $attributes);
        }

        return '';
    }

    /**
     * @param Media|array|null $media
     * @param string           $filter
     * @param bool             $fullPath
     *
     * @return string
     */
    public function mediaPath($media = null, string $filter = null, bool $fullPath = false): string
    {
        $media = $this->denormalize($media);
        if ($media instanceof Media) {
            $provider = $this->providerFactory->getProvider($media);

            return $provider->getPath($media, $filter, $fullPath);
        }

        return '';
    }

    /**
     * @param mixed $media
     *
     * @return mixed
     */
    private function denormalize($media)
    {
        if (is_array($media)) {
            $media = $this->normalizer->denormalize($media, Media::class);
        }

        return $media;
    }
}
