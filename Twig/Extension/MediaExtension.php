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
            new MediaTokenParser(self::class)
        );
    }

    public function getName()
    {
        return 'donjohn_media';
    }

    /**
     * @param Media|null $media
     * @param $filter
     * @param $attributes
     * @return string
     */
    public function media(Media $media = null, $filter = null, array $attributes = array())
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

}
