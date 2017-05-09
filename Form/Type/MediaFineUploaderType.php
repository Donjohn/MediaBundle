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



    public function __construct( $classMedia, FilesystemOrphanageStorage $filesystemOrphanageStorage)
    {
        $this->classMedia = $classMedia;
        $this->filesystemOrphanageStorage = $filesystemOrphanageStorage;
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
//                'prototype' => false,
                ));

        $entryOptionsNormalizer = function (Options $options, $value) {
            $value['mediazone'] = false;
            $value['label'] = false;
            $value['required'] = false;
            $value['multiple'] = false;
            $value['delete_empty'] = false;
            $value['oneup'] = true;
            return $value;
        };

        $resolver->setNormalizer('entry_options', $entryOptionsNormalizer);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $formModifier = function(FormEvent $event) use ($options) {



                $uploadedFiles = $this->filesystemOrphanageStorage->getFiles();

                $data = $event->getData() ?: [] ;
                /** @var \SplFileInfo $file */
            foreach ($uploadedFiles as $file){
                    /** @var Media $media */
                    $media = new $this->classMedia();
                    $media->setBinaryContent( new File($file->getPathname()))
                        ->setProviderName( $options['provider']);
//                    $media=['binaryContent' => new UploadedFile($file->getPathname(), $file->getBasename())];
                    array_push($data,$media);
                }

                $event->setData($data);


                };
        $builder->addEventListener(
                FormEvents::SUBMIT ,
                $formModifier
            );


//        $builder->addEventListener(
//                FormEvents::PRE_SUBMIT ,
//                function(FormEvent $event){
//                    Debug::dump($event->getForm()->getData());
//                }
//            );

    }

}
