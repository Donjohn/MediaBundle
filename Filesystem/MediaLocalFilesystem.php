<?php
/**
 * @author jgn
 * @date 21/03/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Filesystem;


use Donjohn\MediaBundle\Model\Media;
use Gaufrette\Adapter;
use Gaufrette\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class MediaLocalFilesystem extends Filesystem implements MediaFilesystemInterface
{

    /**
     * @var string $uploadFolder
     */
    protected $uploadFolder;


    /**
     * @var string $rootFolder
     */
    protected $rootFolder;

    /**
     * @var Request $request
     */
    protected $request;

    /**
     * MediaLocalFilesystem constructor.
     * @param string $webFolder
     * @param string $uploadFolder
     */
    public function __construct(RequestStack $requestStack, $rootFolder, $uploadFolder)
    {
        parent::__construct(new Adapter\Local($rootFolder, false, '0775'));
        $this->request = $requestStack->getMasterRequest();
        $this->rootFolder = $rootFolder;
        $this->uploadFolder = $uploadFolder;
    }

    /**
     * @return string
     */
    public function getWebPath(Media $media)
    {
        return sprintf('%s%s',
            $this->request->getSchemeAndHttpHost(),
            $this->getPath($media)
            );
    }



    public function getFullPath(Media $media)
    {
        return sprintf('%s%s',
            $this->rootFolder,
            $this->getPath($media)
            );
    }


    public function getPath(Media $media)
    {
        if ($media->getId() === null ) throw new \RuntimeException('media must be psersisted before calling getPath');
        $firstLevel=100000;
        $secondLevel=1000;

        $rep_first_level = (int) ($media->getId() / $firstLevel);
        $rep_second_level = (int) (($media->getId() - ($rep_first_level * $firstLevel)) / $secondLevel);

        return sprintf('%s/%04s/%02s/%s', $this->uploadFolder,  $rep_first_level + 1, $rep_second_level + 1, $media->getFilename() );
    }

    public function hasMedia(Media $media)
    {
        return $this->has($this->getPath($media));
    }

    public function removeMedia(Media $media)
    {
        return $this->hasMedia($media) ? $this->delete($this->getPath($media)) : true;
    }

    public function createMedia(Media $media, File $file)
    {
        return $this->write($this->getPath($media), file_get_contents($file->getRealPath()));
    }


}
