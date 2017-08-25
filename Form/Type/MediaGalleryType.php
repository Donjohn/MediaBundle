<?php
/**
 * Created by PhpStorm.
 * User: tpn
 * Date: 18/04/2017
 * Time: 17:00
 */

namespace Donjohn\MediaBundle\Form\Type;


use Doctrine\ORM\EntityManagerInterface;
use Donjohn\MediaBundle\Form\Transformer\MediaIdTransformer;
use Donjohn\MediaBundle\Model\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class MediaGalleryType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var string
     */
    protected $classMedia;

    public function __construct(EntityManagerInterface $em, $classMedia)
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
            'required' => false,
            'provider' => 'file'
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $vars = $view->vars;
        /** @var Media $media */
        $media = $vars['data'];
        if ($vars['value']) $vars['value'] = $media->getId();
        $vars['provider'] = $options['provider'];
        $view->vars = $vars;
    }
}
