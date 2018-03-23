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
     * @var MediaLiipLocalFilesystem $filesystem
     */
    protected $filesystem;

    /**
     * @inheritdoc
     */
    public function getDownloadResponse(Media $media, array $headers = array(), $filter = null)
    {
        // build the default headers
        $headers = array_merge(array(
            'Content-Type'          => $media->getMimeType(),
            'Content-Disposition'   => sprintf('attachment; filename="%s"', $media->getName()),
        ), $headers);


        return new BinaryFileResponse($this->filesystem->getFullPath($media, $filter), 200, $headers);
    }

    
}
