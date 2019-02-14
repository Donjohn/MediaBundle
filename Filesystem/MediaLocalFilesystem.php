<?php
/**
 * @author Donjohn
 * @date 21/03/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Filesystem;

use Donjohn\MediaBundle\Model\Media;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\RequestContext;

/**
 * Class MediaLocalFilesystem.
 */
class MediaLocalFilesystem implements MediaFilesystemInterface
{
    /**
     * @var string
     */
    protected $uploadFolder;

    /**
     * @var string
     */
    protected $rootFolder;

    /**
     * @var RequestContext
     */
    protected $requestContext;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * MediaLocalFilesystem constructor.
     *
     * @param RequestContext $requestContext
     * @param string         $rootFolder
     * @param string         $uploadFolder
     */
    public function __construct(RequestContext $requestContext, string $rootFolder, string $uploadFolder)
    {
        $this->requestContext = $requestContext;
        $this->rootFolder = $rootFolder;
        $this->uploadFolder = $uploadFolder;
    }

    /**
     * @return Filesystem
     */
    public function createOrGetFilesystem(): Filesystem
    {
        $this->filesystem = $this->filesystem ?: new Filesystem();

        return $this->filesystem;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        $port = '';
        if ('https' === $this->requestContext->getScheme() && 443 !== $this->requestContext->getHttpsPort()) {
            $port = ":{$this->requestContext->getHttpsPort()}";
        }

        if ('http' === $this->requestContext->getScheme() && 80 !== $this->requestContext->getHttpPort()) {
            $port = ":{$this->requestContext->getHttpPort()}";
        }

        $baseUrl = $this->requestContext->getBaseUrl();
        if ('.php' === mb_substr($this->requestContext->getBaseUrl(), -4)) {
            $baseUrl = pathinfo($this->requestContext->getBaseUrl(), PATHINFO_DIRNAME);
        }
        $baseUrl = rtrim($baseUrl, '/\\');

        return sprintf('%s://%s%s%s',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            $port,
            $baseUrl
        );
    }

    /**
     * @param Media $media
     *
     * @return string
     */
    public function getWebPath(Media $media): string
    {
        return sprintf('%s%s',
                        $this->getBaseUrl(),
                        $this->getPath($media)
                    );
    }

    /**
     * @param Media $media
     *
     * @return string
     */
    public function getFullPath(Media $media): string
    {
        return sprintf('%s%s',
            $this->rootFolder,
            $this->getPath($media)
            );
    }

    /**
     * @param Media $media
     *
     * @return string
     */
    public function getPath(Media $media): string
    {
        if (null === $media->getId()) {
            throw new \RuntimeException('media must be persisted before calling getPath');
        }
        $firstLevel = 100000;
        $secondLevel = 1000;

        $rep_first_level = (int) ($media->getId() / $firstLevel);
        $rep_second_level = (int) (($media->getId() - ($rep_first_level * $firstLevel)) / $secondLevel);

        return sprintf('%s/%04s/%02s/%s', $this->uploadFolder, $rep_first_level + 1, $rep_second_level + 1, $media->getFilename());
    }

    /**
     * @param Media $media
     *
     * @return bool
     */
    public function hasMedia(Media $media): bool
    {
        return $this->createOrGetFilesystem()->exists($this->getFullPath($media));
    }

    /**
     * @param Media $media
     *
     * @return bool
     */
    public function removeMedia(Media $media): bool
    {
        if ($this->hasMedia($media)) {
            $this->createOrGetFilesystem()->remove($this->getFullPath($media));
        }

        return true;
    }

    /**
     * @param Media $media
     * @param File  $file
     *
     * @return bool
     */
    public function createMedia(Media $media, File $file): bool
    {
        $this->createOrGetFilesystem()->copy($file->getRealPath(), $this->getFullPath($media));
        $this->createOrGetFilesystem()->remove($file->getRealPath());

        return true;
    }
}
