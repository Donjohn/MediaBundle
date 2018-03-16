<?php
/**
 * @author jgn
 * @date 07/03/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Twig\Extension;


use Donjohn\MediaBundle\Model\MediaInterface;
use Donjohn\MediaBundle\Provider\Exception\NotFoundProviderException;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Donjohn\MediaBundle\Twig\TokenParser\MediaTokenParser;
use Donjohn\MediaBundle\Twig\TokenParser\PathTokenParser;


class MediaExtension extends \Twig_Extension
{
    /**
     * @var ProviderFactory $providerFactory
     */
    protected $providerFactory;

    /**
     * MediaExtension constructor.
     * @param ProviderFactory $providerFactory
     */
    public function __construct(ProviderFactory $providerFactory)
    {
        $this->providerFactory = $providerFactory;
    }


    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(
            new MediaTokenParser(self::class),
            new PathTokenParser(self::class),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('mediaPath', array($this, 'path')),
        );
    }

    public function getName()
    {
        return 'donjohn_media';
    }

    /**
     * @param MediaInterface|null $media
     * @param $filter
     * @param $attributes
     * @return string
     */
    public function media(MediaInterface $media = null, $filter, $attributes)
    {
        if ($media !== null) {
            try {
                $provider = $this->providerFactory->getProvider($media);
                return $provider->render($media, $filter, $attributes);
            }
            catch (NotFoundProviderException $e){}
            catch (\Twig_Error $e){}
        }
        return '';

    }


    public function path(MediaInterface $media = null, $filter)
    {
        if ($media !== null) {
            try {
                $provider = $this->providerFactory->getProvider($media);
                return $provider->getPath($media, $filter);
            }
            catch (NotFoundProviderException $e){}
        }

        return '';


    }

}
