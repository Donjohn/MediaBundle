<?php
/**
 * @author jgn
 * @date 21/03/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Filesystem;


use Gaufrette\Adapter;
use Gaufrette\Filesystem;

class MediaLocalFilesystem extends Filesystem
{
    protected $webFolder;
    protected $uploadFolder;


    public function __construct($webFolder, $uploadFolder)
    {
        $this->webFolder = $webFolder;
        $this->uploadFolder = $uploadFolder;
        parent::__construct(new Adapter\Local($webFolder.$uploadFolder));
    }


    public function getWebFolder()
    {
        return $this->webFolder;
    }

    public function getUploadFolder()
    {
        return $this->uploadFolder;
    }

}