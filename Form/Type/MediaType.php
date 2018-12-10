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
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    /**
     * @var ProviderFactory
     */
    protected $providerFactory;

    /**
     * MediaType constructor.
     *
     * @param ProviderFactory $providerFactory
     */
    public function __construct(ProviderFactory $providerFactory)
    {
        $this->providerFactory = $providerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
//        $emptyData = function (Options $options) {
//            if ($options['multiple']) {
//                return array();
//            }
//
//            return null;
//        };

        $mediaClass = function (Options $options) {
            return $options['multiple'] ? null : $options['media_class'];
        };

        $byReference = function(Options $options)
        {
            return $options['multiple'] ? false : true;
        };

        $resolver->setDefaults(array(
            'translation_domain' => 'DonjohnMediaBundle',
            'provider' => null,
            'label' => 'media',
            'invalid_message' => 'media.error.transform',
            'allow_delete' => true,
            'multiple' => false,
            'required' => false,
            'delete_empty' => true,
            'create_on_update' => true,
            'data_class' => $mediaClass,
            'add_provider_form' => true,
            'by_reference' => $byReference,
            'media_label' => null
        ));
        $resolver->setRequired(['media_class']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (false === $options['multiple']) {

            $builder->addEventSubscriber( new MediaTypeSubscriber($this->providerFactory, $options) );

            $builder->addModelTransformer( new MediaDataTransformer($this->providerFactory, $options['create_on_update'], $options['provider']) );

        } else {

            $mediaOptions = [
                'media_class' => $options['media_class'],
                'required' => $options['required'],
                'provider' => $options['provider'],
                'allow_delete' => $options['allow_delete'],
                'block_name' => 'media',
                'translation_domain' => $options['translation_domain'],
                'by_reference' => true,
            ];

            if (null !== $options['media_label']) $mediaOptions['label'] = $options['media_label'];

            $builder->addEventSubscriber(new MediaCollectionTypeSubscriber($this->providerFactory, $mediaOptions));
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
    }
}
