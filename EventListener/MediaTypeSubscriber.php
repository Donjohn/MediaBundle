<?php

declare(strict_types=1);
/**
 * User: donjo
 * Date: 12/6/2018
 * Time: 2:37 PM.
 */

namespace Donjohn\MediaBundle\EventListener;

use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class MediaTypeSubscriber implements EventSubscriberInterface
{
    /** @var array $options */
    protected $options;

    /** @var ProviderFactory $providerFactory */
    protected $providerFactory;

    public function __construct(ProviderFactory $providerFactory, array $options)
    {
        $this->options = $options;
        $this->providerFactory = $providerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::SUBMIT => 'onSubmit',
        );
    }

    public function preSetData(FormEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getData();
        /** @var FormInterface $form */
        $form = $event->getForm();

        $providerOptions = array('translation_domain' => $this->options['translation_domain'],
            'label' => $this->options['label'],
            'error_bubbling' => true,
            'required' => (null === $media && $this->options['required']),
        );

        if ($media instanceof Media && $this->options['allow_delete']) {
            $form->add('unlink', CheckboxType::class, array(
                'mapped' => false,
                'data' => false,
                'required' => false,
                'label' => 'media.unlink.label',
                'translation_domain' => $this->options['translation_domain'],
            ));
        }

        if ($this->options['add_provider_form'])
        {
            if ($media instanceof Media) {
                $this->providerFactory->getProvider($media->getProviderName() ?? $this->options['provider'])->addEditForm($form, $providerOptions);
            } else {
                $providerAlias = $this->options['provider'] ?? $this->providerFactory->guessProvider(null)->getProviderAlias();
                $provider = $this->providerFactory->getProvider($providerAlias);
                $provider->addCreateForm($form, $providerOptions);
            }
        }
        $form->add('originalFilename', HiddenType::class);
    }

    public function onSubmit(FormEvent $event): void
    {
        if ($event->getForm()->has('unlink') && $event->getForm()->get('unlink')->getData()) {
            $event->setData(null);
        }
    }
}
