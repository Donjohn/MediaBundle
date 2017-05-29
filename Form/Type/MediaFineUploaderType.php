<?php
/**
 * @author jgn
 * @date 12/09/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Form\Type;


use Donjohn\MediaBundle\Model\Media;
use Oneup\UploaderBundle\Uploader\Storage\FilesystemOrphanageStorage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaFineUploaderType extends AbstractType
{
    /** @var FilesystemOrphanageStorage $filesystemOrphanageStorage */
    protected $filesystemOrphanageStorage;

    /**
     * @var string
     */
    protected $classMedia;

    /** @var string */
    protected $chunkSize;



    public function __construct( $classMedia, FilesystemOrphanageStorage $filesystemOrphanageStorage, $chunkSize)
    {
        $this->classMedia = $classMedia;
        $this->filesystemOrphanageStorage = $filesystemOrphanageStorage;
        $this->chunkSize = $chunkSize;
    }

    public function getParent()
    {
        return CollectionType::class;
    }


    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(array(
                'translation_domain' => 'DonjohnMediaBundle',
                'label' => 'medias',
                'allow_delete' => true,
                'allow_add' => true,
                'allow_extra_fields' => true,
                'entry_type' => MediaType::class,
                'provider' => 'file',
                'entry_options' => array(),
                'prototype' => false,
                'required' => true,
                'multiple' => true,
                ));

        $entryOptionsNormalizer = function (Options $options, $value) {
            $value['mediazone'] = false;
            $value['label'] = false;
            $value['required'] = false;
            $value['multiple'] = false;
            $value['delete_empty'] = false;
            $value['oneup'] = true;
            $value['error_bubbling'] = true;
            return $value;
        };

        $resolver->setNormalizer('entry_options', $entryOptionsNormalizer);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder->addEventListener(
                FormEvents::SUBMIT ,
                function (FormEvent $event)use ($options) {

                    $uploadedFiles = $this->filesystemOrphanageStorage->getFiles();

                    $data = $event->getData() ?: [] ;
                    /** @var \SplFileInfo $file */
                    foreach ($uploadedFiles as $file)  {
                        /** @var Media $media */
                        $media = new $this->classMedia();
                        $media->setBinaryContent( new File($file->getPathname()) )
                            ->setProviderName( $options['provider'])
                            ->setOriginalFilename( $file->getBasename());
                        $data[]=$media;
                    }

                    $event->setData($data);
                }
            );

    }

    protected function getChunkMaxSizeBytes()
    {
        $number=substr($this->chunkSize,0,-1);
        switch(strtoupper(substr($this->chunkSize,-1))){
            case "K":
                return $number*1024;
            case "M":
                return $number*pow(1024,2);
            case "G":
                return $number*pow(1024,3);
            case "T":
                return $number*pow(1024,4);
            case "P":
                return $number*pow(1024,5);
            default:
                return $this->chunkSize;
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['chunkSize'] = $this->getChunkMaxSizeBytes();
        $view->vars['multiple'] = $options['multiple'];
    }

}
