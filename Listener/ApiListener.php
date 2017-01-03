<?php
namespace Donjohn\MediaBundle\Listener;

use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Dunglas\ApiBundle\Event\DataEvent;


/**
 * Description of MediaSubscriber
 *
 * @author Donjohn
 */
class ApiListener
{
    private $providerFactory;

    public function __construct(ProviderFactory $providerFactory) {
        $this->providerFactory = $providerFactory;
    }



    /**
     * @param DataEvent $event
     */
    public function onPreCreate(DataEvent $event)
    {
        $data = $event->getData();

        if ($data instanceof Media) {
            $this->providerFactory->getProvider($data->getProviderName())->transform($data);
        }
    }

    /**
     * @param DataEvent $event
     */
    public function onPostCreate(DataEvent $event)
    {
        $data = $event->getData();

        if ($data instanceof Media) {
            $this->providerFactory->getProvider($data->getProviderName())->postLoad($data);
        }
    }
}
