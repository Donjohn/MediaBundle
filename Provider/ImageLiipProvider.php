<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Filesystem\MediaLiipLocalFilesystem;
use Donjohn\MediaBundle\Model\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * description.
 *
 * @author Donjohn
 */
class ImageLiipProvider extends ImageProvider
{
    /**
     * @var MediaLiipLocalFilesystem
     */
    protected $mediaFilesystem;

    /**
     * @param Media $media
     * @param null  $filter
     * @param array $options
     *
     * @return string
     */
    public function render(Media $media, string $filter = null, array $options = array()): string
    {
        return $this->twig->render($this->getTemplate(),
            array('mediaWebPath' => $this->mediaFilesystem->getWebPath($media, $filter),
                    'name' => $media->getName(),
                'options' => $options, )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadResponse(Media $media, array $headers = array(), string $filter = null): Response
    {
        // build the default headers
        $headers = array_merge(array(
            'Content-Type' => $media->getMimeType(),
            'Content-Disposition' => sprintf('attachment; filename="%s"', $media->getName()),
        ), $headers);

        return new BinaryFileResponse($this->mediaFilesystem->getFullPath($media, $filter), 200, $headers);
    }
}
