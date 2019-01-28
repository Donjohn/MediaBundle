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
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class MediaCollectionTypeSubscriber.
 */
class MediaCollectionTypeSubscriber implements EventSubscriberInterface
{
    /** @var array $options */
    protected $options;

    /** @var ProviderFactory $providerFactory */
    protected $providerFactory;

    /** @var StorageInterface|null */
    protected $filesystemOrphanageStorage;

    /**
     * MediaCollectionTypeSubscriber constructor.
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
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
            // (MergeCollectionListener, MergeDoctrineCollectionListener)
            FormEvents::SUBMIT => array('onSubmit', 50),
        );
    }

    /**
     * @param FormEvent $event
     */
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
            $form->add($name, MediaType::class, array_replace($this->options, array(
                'property_path' => '['.$name.']',
                'allow_add' => false,
                'fine_uploader' => false,
            )));
        }

        if (false === $this->options['fine_uploader']) {
            $providerOptions = array('translation_domain' => $this->options['translation_domain'],
                'label' => false,
                'required' => 0 === count($data) && $this->options['required'],
                'multiple' => true,
                'mapped' => false,
                'attr' => ['multiple' => 'multiple'],
            );

            $providerAlias = $options['provider'] ?? $this->providerFactory->guessProvider()->getProviderAlias();
            $provider = $this->providerFactory->getProvider($providerAlias);
            $provider->addCreateForm($form, $providerOptions);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!\is_array($data)) {
            $data = array();
        }

        if (false === $this->options['fine_uploader']) {
            $newMedias = $data['binaryContent'];
            $form->remove('binaryContent');
            unset($data['binaryContent']);
        } else {
            $newMedias = $this->filesystemOrphanageStorage->getFiles($event->getForm()->getName());
        }

        foreach ($newMedias as $uploadedFile) {
            /** @var UploadedFile $uploadedFile */
            $name = count($form->all());
            $data[$name] = ['binaryContent' => $uploadedFile instanceof UploadedFile ? $uploadedFile : new File($uploadedFile->getPathname())];
            $form->add((string) $name, MediaType::class, array_replace($this->options, array(
                'property_path' => '['.$name.']',
                'allow_add' => true,
                'fine_uploader' => false,
                'multiple' => false,
            )));
        }

        $event->setData($data);
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }

        if (!\is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        $previousData = $form->getData();
        /** @var FormInterface $child */
        foreach ($form as $name => $child) {
            if (!$child->getData() instanceof Media) {
                unset($data[$name]);
                $form->remove($name);
            }
        }

        $event->setData($data);
    }
}
