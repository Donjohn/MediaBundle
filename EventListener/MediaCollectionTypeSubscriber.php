<?php

declare(strict_types=1);
/**
 * User: donjo
 * Date: 12/6/2018
 * Time: 2:37 PM.
 */

namespace Donjohn\MediaBundle\EventListener;

use Donjohn\MediaBundle\Form\Type\MediaType;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class MediaCollectionTypeSubscriber implements EventSubscriberInterface
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

    public static function getSubscribedEvents(): array
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
            // (MergeCollectionListener, MergeDoctrineCollectionListener)
            FormEvents::SUBMIT => array('onSubmit', 50),
        );
    }

    public function preSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }

        if (!\is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        foreach ($form as $name => $child) {
            $form->remove($name);
        }

        foreach ($data as $name => $value) {
            $form->add($name, MediaType::class, array_replace(array(
                'property_path' => '['.$name.']',
                'add_provider_form' => false,
            ), $this->options));
        }


        $providerOptions = array('translation_domain' => $this->options['translation_domain'],
            'label' => false,
            'error_bubbling' => true,
            'required' => 0 === count($data) && $this->options['required'],
            'multiple' => true,
            'mapped' => false,
            'attr' => ['multiple' => 'multiple'],
        );

        $providerAlias = $options['provider'] ?? $this->providerFactory->guessProvider(null)->getProviderAlias();
        $provider = $this->providerFactory->getProvider($providerAlias);
        $provider->addCreateForm($form, $providerOptions);
    }

    public function preSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!\is_array($data)) {
            $data = array();
        }

        $newMedias = $data['binaryContent'];
        $data['binaryContent'] = null;

        foreach ($form as $name => $child) {
            if (!isset($data[$name])) {
                $form->remove($name);
            }
        }

        $nbForms = (int) count($data) - 1;
        foreach ($newMedias as $newMedia){
            $data[$nbForms++] = ['binaryContent' => $newMedia];
        }


        // Add all additional rows
        foreach ($data as $name => $value) {
            if (!$form->has($name)) {
                $form->add($name, MediaType::class, array_replace(array(
                    'property_path' => '['.$name.']',
                ), $this->options));
            }
        }

        $event->setData($data);
    }

    public function onSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        // At this point, $data is an array or an array-like object that already contains the
        // new entries, which were added by the data mapper. The data mapper ignores existing
        // entries, so we need to manually unset removed entries in the collection.

        if (null === $data) {
            $data = array();
        }

        if (!\is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        $previousData = $form->getData();
        /** @var FormInterface $child */
        foreach ($form as $name => $child) {
            $isNew = !isset($previousData[$name]);
            $isEmpty = $child->isEmpty();

            // $isNew can only be true if allowAdd is true, so we don't
            // need to check allowAdd again
            if ($isEmpty && $isNew) {
                unset($data[$name]);
                $form->remove($name);
            }
        }

        // The data mapper only adds, but does not remove items, so do this
        // here
        $toDelete = array();

        foreach ($data as $name => $child) {
            if (!$form->has($name)) {
                $toDelete[] = $name;
            }
        }

        foreach ($toDelete as $name) {
            unset($data[$name]);
        }

        $event->setData($data);
    }
}
