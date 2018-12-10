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

    public function addProviderOptions(array $options): array
    {
        $options['constraints'] = array_merge(
            $options['constraints'] ?? [],
            [new Constraints\File(['maxSize' => $this->fileMaxSize, 'mimeTypes' => $this->getAllowedTypes()])]
        );

        return $options;
    }
}
