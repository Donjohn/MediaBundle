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
use Donjohn\MediaBundle\Twig\TokenParser\DownloadTokenParser;
use Donjohn\MediaBundle\Twig\TokenParser\MediaTokenParser;
use Donjohn\MediaBundle\Twig\TokenParser\PathTokenParser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\Common\Util\ClassUtils;


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
     * @var Router $router
     */
    protected $router;

    /**
     * @var array
     */
    protected $entities;

    /**
     * MediaExtension constructor.
     * @param ProviderFactory $providerFactory
     * @param \Twig_Environment $twig
     * @param Router $router
     * @param array $entities
     */
    public function __construct(ProviderFactory $providerFactory, \Twig_Environment $twig, RouterInterface $router, array $entities = array())
    {
        $this->providerFactory = $providerFactory;
        $this->twig = $twig;
        $this->router = $router;
        $this->entities = $entities;
    }


    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(
            new MediaTokenParser(self::class),
            new PathTokenParser(self::class),
            new DownloadTokenParser(self::class),
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

    public function download(Media $media = null)
    {
        try {
            $provider = $this->providerFactory->getProvider($media);
        }
        catch (NotFoundProviderException $e) {
            return '';
        }
        $options = array('id' => $media->getId());
        ;
        if (get_class($media)!=$this->entities[0]) $options['entity'] = ClassUtils::getRealClass(get_class($media));

        return $this->router->generate('donjohn_media_download',$options);

    }

}
