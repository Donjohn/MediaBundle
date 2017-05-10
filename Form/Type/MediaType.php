<?php
/**
 * @author jgn
 * @date 12/09/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Form\Type;


use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Form\Transformer\MediaDataTransformer;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    /**
     * @var ProviderFactory
     */
    protected $providerFactory;

    /**
     * @var string
     */
    protected $classMedia;

    public function __construct( ProviderFactory $providerFactory, $classMedia)
    {
        $this->providerFactory = $providerFactory;
        $this->classMedia = $classMedia;
    }


    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(array(
                'translation_domain' => 'DonjohnMediaBundle',
                'provider' => 'file',
                'mediazone' => true,
                'label' => 'media',
                'invalid_message' => 'media.error.transform',
                'allow_delete' => true,
                'multiple' => false,
                'data_class' => $this->classMedia,
                'required' => false,
                'delete_empty' => true,
                'gallery' => false,
                'oneup' => false,
                ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $media = ($builder->getData() instanceof Media && $builder->getData()->getId()) ? $builder->getData() : null;
        $provider = $this->providerFactory->getProvider($media ? $media->getProviderName() : $options['provider']);


        $formOptions = array('translation_domain' => 'DonjohnMediaBundle',
                            'label' => false,
                            'error_bubbling' => true,
                            'multiple' => $options['multiple'] ? 'multiple' : false,
                            'required' => $options['required'],
                            'attr' => array('class' => $options['oneup']||$options['mediazone'] ? 'hidden' : ''),
                        );
        if ($media) $provider->addEditForm($builder, $formOptions);
        else $provider->addCreateForm($builder, $formOptions);

        $builder->add('originalFilename', HiddenType::class);


        if ($options['allow_delete']){
            $formEventUnlink = function(FormEvent $event) use ($options) {
                if ($event->getData() || $options['multiple']) {
                    $event->getForm()->add('unlink', CheckboxType::class, array(
                        'mapped'   => false,
                        'data'     => false,
                        'required' => false,
                        'label' => !$options['multiple'] ? 'media.unlink.label' : false,
                        'translation_domain' => 'DonjohnMediaBundle'
                    ));
                }
            };

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                $formEventUnlink
            );

            $builder->addEventListener(
                FormEvents::PRE_SUBMIT,
                $formEventUnlink
            );

            $builder->addEventListener(
                FormEvents::SUBMIT,
                function (FormEvent $event) {
                    if ($event->getForm()->has('unlink') && $event->getForm()->get('unlink')->getData()) {
                        $event->setData(null);
                    }
                }
            );
        }

        $builder->addModelTransformer(new MediaDataTransformer($provider, $this->classMedia));

    }

    public function buildView(
        FormView $view,
        FormInterface $form,
        array $options
    ) {
        $view->vars['mediazone'] = $options['mediazone'];
        $view->vars['provider'] = $options['provider'];

    }

}
