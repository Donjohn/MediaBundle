<?php

declare(strict_types=1);
/**
 * User: donjo
 * Date: 12/6/2018
 * Time: 2:37 PM.
 */

namespace Donjohn\MediaBundle\EventListener;

use Donjohn\MediaBundle\Form\Type\MediaType;
use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;
use Oneup\UploaderBundle\Uploader\Storage\StorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class MediaTypeSubscriber.
 */
class MediaTypeSubscriber implements EventSubscriberInterface
{
    /** @var array $options */
    protected $options;

    /** @var ProviderFactory $providerFactory */
    protected $providerFactory;

    /** @var StorageInterface|null */
    protected $filesystemOrphanageStorage;

    /**
     * MediaTypeSubscriber constructor.
     *
     * @param ProviderFactory       $providerFactory
     * @param array                 $options
     * @param StorageInterface|null $filesystemOrphanageStorage
     */
    public function __construct(ProviderFactory $providerFactory, array $options, StorageInterface $filesystemOrphanageStorage = null)
    {
        $this->options = $options;
        $this->providerFactory = $providerFactory;
        $this->filesystemOrphanageStorage = $filesystemOrphanageStorage;
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

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getData();
        /** @var FormInterface $form */
        $form = $event->getForm();

        $providerOptions = array('translation_domain' => $this->options['translation_domain'],
            'label' => false,
            'error_bubbling' => true,
            'required' => null === $media && $this->options['required'] && !$this->options['fine_uploader'],
        );

        if ($media instanceof Media) {
            if ($this->options['allow_delete']) {
                $form->add('unlink', CheckboxType::class, array(
                    'mapped' => false,
                    'data' => false,
                    'required' => false,
                    'label' => $this->options['allow_delete_label'],
                    'translation_domain' => $this->options['translation_domain'],
                ));
            }

            if ($this->options['sortable']) {
                $form->add($this->options['sortable_field'], HiddenType::class, ['empty_data' => 0]);
            }
        }

        if (false === $this->options['fine_uploader'] && $this->options['allow_add']) {
            if ($media instanceof Media) {
                $this->providerFactory->getProvider($media->getProviderName() ?? $this->options['provider'])->addEditForm($form, $providerOptions);
            } else {
                $providerAlias = $this->options['provider'] ?? $this->providerFactory->guessProvider()->getProviderAlias();
                $provider = $this->providerFactory->getProvider($providerAlias);
                $provider->addCreateForm($form, $providerOptions);
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event): void
    {
        if ($event->getForm()->has('unlink') && $event->getForm()->get('unlink')->getData()) {
            $event->setData(null);

            return;
        }

        if ($this->options['fine_uploader'] && false === $this->options['multiple']) {
            /** @var $uploadedFile UploadedFile */
            //on prend de fait le dernier uploadé
            foreach ($this->filesystemOrphanageStorage->getFiles(MediaType::getPathName($event->getForm())) as $uploadedFile) {
                $media = $event->getData();
                if (null === $media) {
                    $dataClass = $event->getForm()->getConfig()->getOption('data_class');
                    $media = new $dataClass();
                }
                $media->setBinaryContent(new UploadedFile($uploadedFile->getPathname(), $uploadedFile->getBasename()));
                $event->setData($media);
            }
        }
    }
}
