<?php

declare(strict_types=1);

namespace Donjohn\MediaBundle\Routing\Loader;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\VarDumper\VarDumper;

/**
 * User: donjo
 * Date: 12/20/2018
 * Time: 5:34 PM.
 */
class DonjohnMediaLoader implements LoaderInterface
{
    /**
     * @var string|null
     */
    private $mappingName;

    /**
     * DonjohnMediaLoader constructor.
     *
     * @param LoaderInterface $loader
     * @param string|null $mappingName
     */
    public function __construct(LoaderInterface $loader, ?string $mappingName = null)
    {
        $this->loader = $loader;
        $this->mappingName = $mappingName;
    }

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @param mixed $resource
     * @param null  $type
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null): RouteCollection
    {
        /** @var RouteCollection $routes */
        $routes = $this->loader->load($resource, $type);

        $routes->remove('_uploader_cancel_'.$this->mappingName);

        $routes->add('_uploader_cancel_'.$this->mappingName,
            new Route('_uploader/'.$this->mappingName.'/cancel/{id}',
                ['_controller' => 'oneup_uploader.controller.'.$this->mappingName.'::cancel',
                    'id' => null,
                ],
                [], [], null, [], ['POST', 'DELETE'])
        );

        $routes->add('_uploader_init_'.$this->mappingName,
            new Route('_uploader/'.$this->mappingName.'/init',
                ['_controller' => 'oneup_uploader.controller.'.$this->mappingName.'::init'],
                [], [], null, [], ['GET'])
        );

        return $routes;
    }

    /**
     * @param $resource
     * @param null $type
     *
     * @return bool
     */
    public function supports($resource, $type = null): bool
    {
        return $this->loader->supports($resource, $type);
    }

    /**
     * Gets the loader resolver.
     *
     * @return LoaderResolverInterface A LoaderResolverInterface instance
     */
    public function getResolver(): LoaderResolverInterface
    {
        return $this->loader->getResolver();
    }

    /**
     * Sets the loader resolver.
     */
    public function setResolver(LoaderResolverInterface $resolver): void
    {
        $this->loader->setResolver($resolver);
    }
}
