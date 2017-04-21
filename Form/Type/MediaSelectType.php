<?php
/**
 * Created by PhpStorm.
 * User: tpn
 * Date: 18/04/2017
 * Time: 17:00
 */

namespace Donjohn\MediaBundle\Form\Type;


use Donjohn\MediaBundle\Form\Transformer\MediaIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaSelectType extends AbstractType
{

    protected $em;

    /**
     * @var string
     */
    protected $classMedia;

    public function __construct($em, $classMedia)
    {
        $this->em = $em;
        $this->classMedia = $classMedia;
    }

    public function getParent()
    {
        return TextType::class;
    }

     public function buildForm(FormBuilderInterface $builder, array $options)
     {
         $builder->addModelTransformer(new MediaIdTransformer($this->em, $this->classMedia));
     }

    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(array(
            'translation_domain' => 'DonjohnMediaBundle',
            'label' => 'media',
            'data_class' => $this->classMedia,
            'required' => false
        ));
    }
}