<?php

declare(strict_types=1);

namespace Donjohn\MediaBundle\Routing\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * User: donjo
 * Date: 12/20/2018
 * Time: 5:34 PM.
 */
class DonjohnMediaLoader extends Loader
{
    /** @var null|string $mapping_name */
    private $mapping_name;

    /**
     * DonjohnMediaLoader constructor.
     *
     * @param string|null $mapping_name
     */
    public function __construct(string $mapping_name = null)
    {
        $this->mapping_name = $mapping_name;
    }

    /**
     * @var bool
     */
    private $isLoaded = false;

    /**
     * @param mixed $resource
     * @param null  $type
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null): RouteCollection
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();

        if (null !== $this->mapping_name) {
            // add the new route to the route collection
            $routes->add('_uploader_cancel_'.$this->mapping_name,
                    new Route('_uploader/'.$this->mapping_name.'/cancel/{id}',
                                ['_controller' => 'oneup_uploader.controller.'.$this->mapping_name.'::cancelFineUploader',
                                    'id' => null,
                                ],
                    [], [], null, [], ['DELETE'])
                    );

            $routes->add('_uploader_init_'.$this->mapping_name,
                new Route('_uploader/'.$this->mapping_name.'/init',
                    ['_controller' => 'oneup_uploader.controller.'.$this->mapping_name.'::initFineUploader'],
                    [], [], null, [], ['GET'])
            );
        }

        $this->isLoaded = true;

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
        return 'donjohn_media' === $type;
    }
}
