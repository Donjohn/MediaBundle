<?php
/**
 * @author Donjohn
 * @date 22/03/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Uploader\Storage;

use Oneup\UploaderBundle\Uploader\Chunk\Storage\ChunkStorageInterface;
use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Storage\StorageInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Oneup\UploaderBundle\Uploader\Storage\FilesystemOrphanageStorage as BaseFilesystemOrphanageStorage;

/**
 * Class FilesystemOrphanageStorage.
 */
class FilesystemOrphanageStorage extends BaseFilesystemOrphanageStorage
{
    /**
     * @var mixed
     */
    protected $directory;

    /**
     * FilesystemOrphanageStorage constructor.
     *
     * @param StorageInterface      $storage
     * @param SessionInterface      $session
     * @param ChunkStorageInterface $chunkStorage
     * @param array                 $config
     * @param string                $type
     */
    public function __construct(StorageInterface $storage, SessionInterface $session, ChunkStorageInterface $chunkStorage, array $config, string $type)
    {
        parent::__construct($storage, $session, $chunkStorage, $config, $type);
        $this->directory = $config['directory'];
    }

    /**
     * @param FileInterface $file
     * @param $name
     * @param null $path
     *
     * @return \SplFileInfo
     */
    public function upload(FileInterface $file, $name, $path = null): \SplFileInfo
    {
        if (!$this->session->isStarted()) {
            throw new \RuntimeException('You need a running session in order to run the Orphanage.');
        }

        $path = sprintf('%s/%s/%s', $this->directory, $this->getPath($path), $name);

        // now that we have the correct path, compute the correct name
        // and target directory
        $targetName = basename($path);
        $targetDir = dirname($path);

        $file = $file->move($targetDir, $targetName);

        return $file;
    }

    /**
     * @param string $path
     *
     * @return Finder
     */
    public function getFiles(string $path = null): Finder
    {
        $finder = new Finder();
        try {
            $finder->in($this->getFindPath($path))->files();
        } catch (\InvalidArgumentException $e) {
            //catch non-existing directory exception.
            //This can happen if getFiles is called and no file has yet been uploaded

            //push empty array into the finder so we can emulate no files found
            $finder->append([]);
        }

        return $finder;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function getPath(string $path = null): string
    {
        return $path ? sprintf('%s/%s/%s', $this->session->getId(), $this->type, $path)
                    : sprintf('%s/%s', $this->session->getId(), $this->type);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function getFindPath(string $path = null): string
    {
        return sprintf('%s/%s', $this->config['directory'], $this->getPath($path));
    }
}
