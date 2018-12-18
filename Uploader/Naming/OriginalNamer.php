<?php
/**
 * @author Donjohn
 * @date 22/03/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Uploader\Naming;

use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Naming\NamerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class OriginalNamer.
 */
class OriginalNamer implements NamerInterface
{
    /**
     * @param FileInterface $file
     *
     * @return string|null
     */
    public function name(FileInterface $file): ?string
    {
        /* @var UploadedFile $file */
        return $file->getClientOriginalName();
    }
}
