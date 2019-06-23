<?php

declare(strict_types=1);
/**
 * @author Donjohn
 * @date 16/04/2018
 * @description For ...
 */

namespace Donjohn\MediaBundle\Fixtures\Faker\Provider;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class BinaryContentProvider.
 */
class MediaBinaryContentProvider
{
    /**
     * @var
     */
    protected $defaultSourceDirectory;

    /**
     * BinaryContentProvider constructor.
     *
     * @param string $sourceDirectory
     */
    public function __construct(string $sourceDirectory)
    {
        $this->defaultSourceDirectory = $sourceDirectory;
    }

    /**
     * @param string|null $source can ba file, or folder or null (takes default folder)
     *
     * @return UploadedFile
     */
    public function mediaBinaryContent(string $source = null): UploadedFile
    {
        $sourceFullPath = null;
        if (null !== $source && is_file($source)) {
            $sourceFullPath = new \SplFileInfo($source);
        }

        if (null === $sourceFullPath) {
            $sourceDirectory = $this->defaultSourceDirectory;
            if (null !== $source && is_dir($source)) {
                $sourceDirectory = $source;
            }

            $finder = new Finder();
            $finder->in($sourceDirectory)->files();
            if (0 === $finder->count()) {
                throw new \InvalidArgumentException(sprintf('Source directory %s is empty.', $sourceDirectory));
            }
            $arrayFiles = iterator_to_array($finder);
            shuffle($arrayFiles);

            /** @var \SplFileInfo $sourceFullPath */
            $sourceFullPath = array_shift($arrayFiles);
        }

        $copyFullPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.pathinfo($sourceFullPath->getRealPath(), PATHINFO_FILENAME).uniqid('media', false).'.'.pathinfo($sourceFullPath->getRealPath(), PATHINFO_EXTENSION);
        copy($sourceFullPath->getRealPath(), $copyFullPath);

        return new UploadedFile($copyFullPath, pathinfo($sourceFullPath->getRealPath(), PATHINFO_BASENAME));
    }
}
