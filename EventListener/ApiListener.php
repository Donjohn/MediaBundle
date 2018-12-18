<?php
/**
 * @author Donjohn
 * @date 29/08/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\EventListener;

use Donjohn\MediaBundle\Form\Transformer\MediaDataTransformer;
use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * Class ApiListener.
 */
class ApiListener
{
    /** @var ProviderFactory $providerFactory */
    protected $providerFactory;

    /**
     * ApiListener constructor.
     *
     * @param ProviderFactory $providerFactory
     */
    public function __construct(ProviderFactory $providerFactory)
    {
        $this->providerFactory = $providerFactory;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event): void
    {
        $request = $event->getRequest();

        $media = $event->getControllerResult();

        if (!$media instanceof Media || (Request::METHOD_POST !== $request->getMethod() && Request::METHOD_PUT !== $request->getMethod())) {
            return;
        }
        $mediaDataTransformer = new MediaDataTransformer($this->providerFactory, null, false);
        $mediaTransformed = $mediaDataTransformer->reverseTransform($media);
        $event->setControllerResult($mediaTransformed);
    }
}
