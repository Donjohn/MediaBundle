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
use Symfony\Component\OptionsResolver\Options;
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

        $formModifier = function(FormEvent $event){
                    $newData = []; $j=0;
                    if ($event->getData()) {
                        foreach ($event->getData() as $media)
                        {
                            if (!isset($media['binaryContent'])) continue;
                            if (is_array($media['binaryContent'])) {
                                $totalBinary = count($media['binaryContent']);
                                for ($i=0; $i<$totalBinary; $i++) {
                                    if (!empty($media['binaryContent'][$i])) {
                                        $cloneMedia = $media;
                                        $cloneMedia['binaryContent'] = $media['binaryContent'][$i];
                                        if ($cloneMedia['binaryContent']) $newData[++$j]=$cloneMedia;
                                    }
                                }
                            } else {
                                $newData[++$j] = $media;
                            }
                        }

                        $event->setData($newData);

                    }

                };
        $builder->addEventListener(
                FormEvents::PRE_SUBMIT ,
                $formModifier
            );

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
                'mediazone' => true,
                'entry_options' => array()
                ));

        $entryOptionsNormalizer = function (Options $options, $value) {
            $value['mediazone'] = false;
            $value['label'] = false;
            $value['required'] = false;
            $value['multiple'] = true;
            $value['delete_empty'] = false;
            $value['error_bubbling'] = true;
            return $value;
        };

        $resolver->setNormalizer('entry_options', $entryOptionsNormalizer);
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
