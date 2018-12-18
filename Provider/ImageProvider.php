<?php

namespace Donjohn\MediaBundle\Provider;

use Symfony\Component\Validator\Constraints;

/**
 * description.
 *
 * @author Donjohn
 */
class ImageProvider extends FileProvider
{
    /**
     * @return string alias
     */
    public function getAlias(): string
    {
        return 'image';
    }

    /**
     * @return array
     */
    public function getAllowedTypes(): array
    {
        return ['image/bmp', 'image/gif', 'image/jpeg', 'image/jpg', 'image/png', 'image/tiff', 'image/jpeg', 'image/png'];
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function addProviderOptions(array $options): array
    {
        $options['constraints'] = array_merge(
            $options['constraints'] ?? [],
            [new Constraints\File(['maxSize' => $this->getFileMaxSize(), 'mimeTypes' => $this->getAllowedTypes()])]
        );

        return $options;
    }
}
