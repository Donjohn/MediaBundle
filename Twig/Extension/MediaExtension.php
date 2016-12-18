<?php
/**
 * @author jgn
 * @date 07/03/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Twig\Extension;


use Donjohn\MediaBundle\Model\Media;
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
     * @var \Twig_Environment $twig
     */
    protected $twig;

    /**
     * MediaExtension constructor.
     * @param \Donjohn\MediaBundle\Provider\Factory\ProviderFactory $providerFactory
     */
    public function __construct(ProviderFactory $providerFactory, \Twig_Environment $twig)
    {
        $this->providerFactory = $providerFactory;
        $this->twig = $twig;
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

    public function getName()
    {
        return 'donjohn_media';
    }

    public function media(Media $media = null, $filter, $attributes)
    {

        try {
            $provider = $this->providerFactory->getProvider($media);
        }
        catch (NotFoundProviderException $e) {
            return '';
        }
        $attributes = array_merge($attributes, array('filter' => $filter));
        return $provider->render($this->twig, $media, $attributes);

    }


    public function path(Media $media = null, $filter)
    {
        try {
            $provider = $this->providerFactory->getProvider($media);
        }
        catch (NotFoundProviderException $e) {
            return '';
        }
        return $provider->getPath($media, $filter);

    }

}
