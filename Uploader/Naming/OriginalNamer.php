<?php
/**
 * @author jgn
 * @date 22/03/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Uploader\Naming;

use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Naming\NamerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OriginalNamer implements NamerInterface
{
    public function name(FileInterface $file)
    {
        /** @var UploadedFile $file */
        return $file->getClientOriginalName();
    }
}
