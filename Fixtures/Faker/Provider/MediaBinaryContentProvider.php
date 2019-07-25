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
        $sourceFullPath = $source ?: $this->defaultSourceDirectory;

        if (is_dir($sourceFullPath)) {
            $finder = new Finder();
            $finder->in($sourceFullPath)->files();
            if (0 === $finder->count()) {
                throw new \InvalidArgumentException(sprintf('Source directory %s is empty.', $sourceFullPath));
            }
            $arrayFiles = iterator_to_array($finder);
            shuffle($arrayFiles);

            /** @var \SplFileInfo $sourceFullPath */
            $sourceFullPath = array_shift($arrayFiles);

        } elseif (!is_file($sourceFullPath)) {
            throw new \RuntimeException(sprintf('%s does not exist', $sourceFullPath));
        } else {
            $sourceFullPath = new \SplFileInfo($sourceFullPath);
        }

        $copyFullPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.pathinfo($sourceFullPath->getRealPath(), PATHINFO_FILENAME).uniqid('media', false).'.'.pathinfo($sourceFullPath->getRealPath(), PATHINFO_EXTENSION);
        copy($sourceFullPath->getRealPath(), $copyFullPath);

        return new UploadedFile($copyFullPath, pathinfo($sourceFullPath->getRealPath(), PATHINFO_BASENAME));
    }
}
