<?php
/**
 * @author jgn
 * @date 12/09/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaCollectionType extends AbstractType
{
    /**
     * @var string
     */
    protected $classMedia;

    public function __construct( $classMedia )
    {
        $this->classMedia = $classMedia;
    }

    public function getParent()
    {
        return CollectionType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $options['entry_options'] = array_merge($options['entry_options'], array(
                                                                'provider' => $options['provider']
                                                ));


        $builder->addEventListener(
                FormEvents::PRE_SUBMIT,
                function(FormEvent $event){
                    $newData = []; $j=0;
                    if ($event->getData()) {
                        foreach ($event->getData() as $media)
                        {
                            if (!isset($media['binaryContent'])) continue;
                            if (is_array($media['binaryContent'])) {
                                for ($i=0; $i<count($media['binaryContent']); $i++) {
                                    $cloneMedia = $media;
                                    $cloneMedia['binaryContent'] = $media['binaryContent'][$i];
                                    if ($cloneMedia['binaryContent']) $newData[++$j]=$cloneMedia;
                                }
                            } else {
                                $newData[++$j] = $media;
                            }
                        }
                        $event->setData($newData);
                    }

                }
            );


    }


    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(array(
                'translation_domain' => 'DonjohnMediaBundle',
                'error_bubbling' => true,
                'label' => 'medias',
                'allow_delete' => true,
                'allow_add' => true,
                'allow_extra_fields' => true,
                'entry_type' => MediaType::class,
                'dropzone' => true,
                'provider' => 'file',
                'entry_options' => array(
                                    'dropzone' => false,
                                    'label' => false,
                                    'required' => false,
                                    'multiple' => true
                                )
                ));
    }

    public function buildView(
        FormView $view,
        FormInterface $form,
        array $options
    ) {
        $view->vars['dropzone'] = $options['dropzone'];
        $view->vars['provider'] = $options['provider'];

    }

}
