<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Filesystem\MediaLiipLocalFilesystem;
use Donjohn\MediaBundle\Model\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


/**
 * description 
 * @author Donjohn
 */
class ImageLiipProvider extends ImageProvider {
    
    /**
     * @var MediaLiipLocalFilesystem $mediaFilesystem
     */
    protected $mediaFilesystem;

    /**
     * @param Media $media
     * @param null $filter
     * @param array $options
     * @return string
     */
    public function render(Media $media, $filter = 'reference', array $options = array()){
        return $this->twig->render($this->getTemplate(),
            array('mediaWebPath' => $this->mediaFilesystem->getWebPath($media, $filter),
                'options' => $options)
        );
    }

    /**
     * @inheritdoc
     */
    public function getDownloadResponse(Media $media, $headers = array(), $filter = null)
    {
        // build the default headers
        $headers = array_merge(array(
            'Content-Type'          => $media->getMimeType(),
            'Content-Disposition'   => sprintf('attachment; filename="%s"', $media->getName()),
        ), $headers);


        return new BinaryFileResponse($this->mediaFilesystem->getFullPath($media, $filter), 200, $headers);
    }

    
}
