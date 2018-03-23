<?php

namespace Donjohn\MediaBundle\Provider;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

/**
 * description 
 * @author Donjohn
 */
class ImageProvider extends FileProvider  {
    
    /**
     * @return string alias
     */
    public function getAlias()
    {
        return 'image';
    }

    /**
     * @inheritdoc
     */
    public function addEditForm(FormBuilderInterface $builder, array $options)
    {
        $options['constraints'] = array(new File([
                        'maxSize' => $this->fileMaxSize,
                        'mimeTypes' => $this->getAllowedTypes()
                    ]));
        $builder->add('binaryContent', FileType::class, $options );
    }

    /**
     * @inheritdoc
     */
    public function addCreateForm(FormBuilderInterface $builder, array $options)
    {
        $options['constraints'] = array(new File([
                        'maxSize' => $this->fileMaxSize,
                        'mimeTypes' => $this->getAllowedTypes()
                    ]));
        $builder->add('binaryContent', FileType::class, $options );
    }

    
}
