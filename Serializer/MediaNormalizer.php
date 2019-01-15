<?php

declare(strict_types=1);

namespace Donjohn\MediaBundle\Serializer;

use Donjohn\MediaBundle\Model\Media;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * User: donjo
 * Date: 12/28/2018
 * Time: 10:17 AM.
 */
class MediaNormalizer implements NormalizerInterface
{
    /**
     * @var ObjectNormalizer
     */
    private $objectNormalizer;

    /**
     * MediaNormalizer constructor.
     *
     * @param ObjectNormalizer $objectNormalizer
     */
    public function __construct(ObjectNormalizer $objectNormalizer)
    {
        $this->objectNormalizer = $objectNormalizer;
    }

    /**
     * @param mixed|Media $object
     * @param null        $format
     * @param array       $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = array()): array
    {
        $data = $this->objectNormalizer->normalize($object, $format, $context);

        if (!isset($data['id'])) {
            $data['id'] = $object->getId();
        }
        if (!isset($data['filename'])) {
            $data['filename'] = $object->getFilename();
        }
        if (!isset($data['providerName'])) {
            $data['providerName'] = $object->getProviderName();
        }

        return $data;
    }

    /**
     * @param mixed $data
     * @param null  $format
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Media;
    }
}
