<?php
/**
 * @author jgn
 * @date 12/09/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Form;


use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Form\Transformer\MediaDataTransformer;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    /**
     * @var ProviderFactory
     */
    protected $providerFactory;

    public function __construct( ProviderFactory $providerFactory)
    {
        $this->providerFactory = $providerFactory;
    }


    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(array(
                'translation_domain' => 'DonjohnMediaBundle',
                'error_bubbling' => true,
                'provider' => 'file',
                'dropzone' => false,
                'redirect' => false,
                'maxFiles' => 1,
                'label' => 'media',
                'invalid_message' => 'media.error.transform',

                ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if ($options['data_class'] === null ) throw new MissingOptionsException('you must define data_class');

        $media = ($builder->getData() instanceof Media && $builder->getData()->getId()) ? $builder->getData() : null;
        $provider = $this->providerFactory->getProvider($media ? $media->getProviderName() : $options['provider']);

        if ($media) $provider->addEditForm($builder, $options);
        else $provider->addCreateForm($builder, $options);

        $builder->addModelTransformer(new MediaDataTransformer($provider));

    }

    public function buildView(
        FormView $view,
        FormInterface $form,
        array $options
    ) {
        $view->vars['dropzone'] = $options['dropzone'];
        $view->vars['redirect'] = $options['redirect'];
        $view->vars['maxFiles'] = $options['maxFiles'];
    }

}