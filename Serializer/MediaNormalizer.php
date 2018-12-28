<?php

declare(strict_types=1);

namespace Donjohn\MediaBundle\Serializer;

use Doctrine\Common\Util\ClassUtils;
use Donjohn\MediaBundle\Model\Media;
use function GuzzleHttp\Psr7\parse_request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * User: donjo
 * Date: 12/28/2018
 * Time: 10:17 AM.
 */
class MediaNormalizer extends ObjectNormalizer
{



    /**
     * @param mixed $object
     * @param null  $format
     * @param array $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = array()): array
    {

        //si pas de group, mapping par default.
        if (!isset($context[parent::GROUPS]) || !is_array($context[parent::GROUPS]))
        {
            $context[parent::ATTRIBUTES] = ['id', 'filename', 'providerName'];
        }
        /** @var Media $object*/
        $data = parent::normalize($object, $format, $context);
        if (!isset($data['class'])) $data['class'] = ClassUtils::getRealClass(\get_class($object));
        if (!isset($data['id']) || !isset($data['filename']) || !isset($data['providerName']))
        {
            throw new \RuntimeException(sprintf('pls add id, filename and providerName to your serializer mapping for class %s',
                $data['class']
                ));
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
