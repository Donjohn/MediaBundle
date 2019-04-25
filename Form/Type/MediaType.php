<?php
/**
 * @author Donjohn
 * @date 12/09/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Form\Type;

use Donjohn\MediaBundle\EventListener\MediaCollectionTypeSubscriber;
use Donjohn\MediaBundle\EventListener\MediaTypeSubscriber;
use Donjohn\MediaBundle\Form\Transformer\MediaDataTransformer;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Donjohn\MediaBundle\Provider\FileProvider;
use Donjohn\MediaBundle\Repository\MimeTypeRepository;
use Oneup\UploaderBundle\Uploader\Storage\StorageInterface;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MediaType.
 */
class MediaType extends AbstractType
{
    /**
     * @var ProviderFactory
     */
    protected $providerFactory;

    /** @var string $fineUploaderTemplate */
    protected $fineUploaderTemplate;

    /** @var StorageInterface|null */
    protected $filesystemOrphanageStorage;

    /** @var string $oneupMappingName */
    protected $oneupMappingName;

    /**
     * MediaType constructor.
     *
     * @param ProviderFactory       $providerFactory
     * @param string                $fineUploaderTemplate
     * @param string                $oneupMappingName
     * @param StorageInterface|null $filesystemOrphanageStorage
     */
    public function __construct(ProviderFactory $providerFactory, string $fineUploaderTemplate, string $oneupMappingName, StorageInterface $filesystemOrphanageStorage = null)
    {
        $this->providerFactory = $providerFactory;
        $this->fineUploaderTemplate = $fineUploaderTemplate;
        $this->oneupMappingName = $oneupMappingName;
        $this->filesystemOrphanageStorage = $filesystemOrphanageStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $mediaClass = function (Options $options) {
            return $options['multiple'] ? null : $options['media_class'];
        };

        $allowExtraFields = function (Options $options) {
            return $options['multiple'];
        };

        $resolver->setDefaults(array(
            'translation_domain' => 'DonjohnMediaBundle',
            'provider' => null,
            'label' => 'media',
            'invalid_message' => 'media.error.transform',
            'allow_delete' => true,
            'allow_delete_label' => 'media.unlink.label',
            'allow_add' => true,
            'allow_extra_fields' => $allowExtraFields,
            'multiple' => false,
            'required' => false,
            'delete_empty' => true,
            'create_on_update' => true,
            'data_class' => $mediaClass,
            'media_label' => null,
            'fine_uploader_template' => $this->fineUploaderTemplate,
            'fine_uploader' => false,
            'show_template' => 'DonjohnMediaBundle:Form:media_form_show.html.twig',
            'sortable' => false,
            'sortable_field' => 'position',
            'session_init' => true,
            'thumbnail_format' => 'thumbnail',
        ));
        $resolver->setRequired(['media_class']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (false === $options['multiple']) {
            $builder->addEventSubscriber(new MediaTypeSubscriber($this->providerFactory, $options, $this->filesystemOrphanageStorage));

            $builder->addModelTransformer(new MediaDataTransformer($this->providerFactory, $options['create_on_update'], $options['provider']));
        } else {
            $mediaOptions = [
                'media_class' => $options['media_class'],
                'required' => $options['fine_uploader'] ? false : $options['required'],
                'provider' => $options['provider'],
                'allow_delete' => $options['allow_delete'],
                'allow_delete_label' => $options['allow_delete_label'],
                'allow_add' => $options['allow_add'],
                'block_name' => 'media',
                'translation_domain' => $options['translation_domain'],
                'by_reference' => true,
                'fine_uploader' => $options['fine_uploader'],
                'show_template' => $options['show_template'],
                'sortable' => $options['sortable'],
                'sortable_field' => $options['sortable_field'],
                'session_init' => $options['session_init'],
                'thumbnail_format' => $options['thumbnail_format'],
            ];

            if (null !== $options['media_label']) {
                $mediaOptions['label'] = $options['media_label'];
            }

            $builder->addEventSubscriber(new MediaCollectionTypeSubscriber($this->providerFactory, $mediaOptions, $this->filesystemOrphanageStorage));
            $builder
                ->addEventSubscriber(new MergeDoctrineCollectionListener())
                ->addViewTransformer(new CollectionToArrayTransformer(), true)
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['show_template'] = $options['show_template'];
        $view->vars['thumbnail_format'] = $options['thumbnail_format'];            
        if ($options['fine_uploader']) {
            $view->vars['fine_uploader'] = $options['fine_uploader'];
            $view->vars['session_init'] = $options['session_init'];
            $view->vars['fine_uploader_template'] = $options['fine_uploader_template'];
            $view->vars['oneup_mapping'] = $this->oneupMappingName;
            $view->vars['form_name'] = MediaType::getPathName($form);
            /** @var FileProvider $provider */
            $provider = $options['provider'] ? $this->providerFactory->getProvider($options['provider']) : $this->providerFactory->getProvider('file');
            $view->vars['chunk_size'] = $provider->getFileMaxSize();
            $view->vars['validation_accept_files'] = implode(',', $provider->getAllowedTypes());
            $view->vars['validation_allowed_extensions'] = "['".implode("','", MimeTypeRepository::findExtensions($provider->getAllowedTypes()))."']";

        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @return string
     */
    public static function getPathName(FormInterface $form): string
    {
        $name = $form->getName();
        $parent = $form->getParent();
        while ($parent)
        {
            $name = $parent->getName().'_'.$name;
            $parent = $parent->getParent();
        }
        return $name;

    }
}
