<?php

namespace Donjohn\MediaBundle\Form\Transformer;

use Donjohn\MediaBundle\Provider\ProviderInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Donjohn\MediaBundle\Model\Media;


class MediaDataTransformer implements DataTransformerInterface
{
    /**
     * @var \Donjohn\MediaBundle\Provider\ProviderInterface
     */
    protected $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($oMedia)
    {
        if (!($oMedia instanceof Media) || (!$oMedia->getBinaryContent() instanceof \SplFileInfo)) return $oMedia;

        $this->provider->transform($oMedia);

        return $oMedia;
    }
}

