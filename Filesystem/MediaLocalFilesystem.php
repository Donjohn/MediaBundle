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


    public function __construct($webfolder)
    {
        $this->webFolder = $webfolder;
        parent::__construct(new Adapter\Local($webfolder));
    }


    public function getWebFolder()
    {
        return $this->webFolder;
    }

}