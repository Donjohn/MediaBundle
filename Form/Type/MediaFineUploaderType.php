<?php
/**
 * @author Donjohn
 * @date 12/09/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Form\Type;


use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Oneup\UploaderBundle\Uploader\Storage\FilesystemOrphanageStorage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaFineUploaderType extends AbstractType
{
    /** @var FilesystemOrphanageStorage $filesystemOrphanageStorage */
    protected $filesystemOrphanageStorage;

    /** @var  ProviderFactory $providerFactory */
    protected $providerFactory;

    /** @var string $chunkSize */
    protected $chunkSize;


    /**
     * MediaFineUploaderType constructor.
     * @param FilesystemOrphanageStorage $filesystemOrphanageStorage
     * @param ProviderFactory $providerFactory
     * @param string $chunkSize
     */
    public function __construct($chunkSize, FilesystemOrphanageStorage $filesystemOrphanageStorage, ProviderFactory $providerFactory)
    {
        $this->filesystemOrphanageStorage = $filesystemOrphanageStorage;
        $this->providerFactory = $providerFactory;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(array(
                'translation_domain' => 'DonjohnMediaBundle',
                'label' => 'medias',
                'allow_delete' => true,
                'allow_add' => true,
                'allow_extra_fields' => true,
                'entry_options' => array(),
                'prototype' => false,
                'required' => true,
                'multiple' => true,
                'oneup_mapping' => 'medias',
                'provider' => null,
                'by_reference' => false,
                'entry_type' => MediaType::class
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

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA ,
            function(FormEvent $event) use ($options) {
                if (!$options['multiple'] &&
                    !$event->getData() instanceof \Traversable
                )
                    $event->setData(new ArrayCollection($event->getData()));
            });

        $builder->addEventListener(
                FormEvents::SUBMIT ,
                function (FormEvent $event) use ($options) {

                    $uploadedFiles = $this->filesystemOrphanageStorage->getFiles();

                    $data = $event->getData() ?: [] ;
                    /** @var \SplFileInfo $file */
                    foreach ($uploadedFiles as $uploadedFile)  {
                        /** @var Media $media */
                        /** @var UploadedFile $uploadedFile */
                        $file = new File($uploadedFile->getPathname());

                        $media = new $options['entry_options']['data_class'];
                        $media->setBinaryContent( $file )
                            ->setProviderName( $options['provider'] ?: $this->providerFactory->guessProvider($file)->getProviderAlias())
                            ->setOriginalFilename( $file->getBasename());
                        $data[]=$media;
                    }

                    $event->setData($data);
                }
            );

    }

    /**
     * @return bool|string
     */
    static function getChunkMaxSizeBytes($chunkSize)
    {
        $number=substr($chunkSize,0,-1);
        switch(strtoupper(substr($chunkSize,-1))){
            case 'K':
                return $number*1024;
            case 'M':
                return $number* (1024 ** 2);
            case 'G':
                return $number* (1024 ** 3);
            case 'T':
                return $number* (1024 ** 4);
            case 'P':
                return $number* (1024 ** 5);
            default:
                return $chunkSize;
        }
    }

    /**
     * @inheritdoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['chunkSize'] = min([$this->getChunkMaxSizeBytes($this->chunkSize), $this->getChunkMaxSizeBytes(ini_get('upload_max_filesize')), $this->getChunkMaxSizeBytes(ini_get('post_max_size'))]);
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['oneup_mapping'] = $options['oneup_mapping'];
    }

}
