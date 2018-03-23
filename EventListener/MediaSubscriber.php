<?php
namespace Donjohn\MediaBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Donjohn\MediaBundle\Model\Media;
use Donjohn\MediaBundle\Provider\Factory\ProviderFactory;

/**
 * Description of MediaSubscriber
 *
 * @author Donjohn
 */
class MediaSubscriber implements EventSubscriber {
    
    private $providerFactory;
    
    public function __construct(ProviderFactory $providerFactory) {
        $this->providerFactory = $providerFactory;
    }

    /**
     * @inheritdoc
     */
    public function getSubscribedEvents() {
        return array(
            'postLoad',
            'prePersist',
            'postPersist',
            'preUpdate',
            'postUpdate',
            'preRemove',
        );
    }

    /**
     * event declenché à lecture de l'objet, sert à loader les paths
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args) {
        $media = $args->getEntity();
        if ($media instanceof Media )$this->providerFactory->getProvider($media)->postLoad($media);
    }

    /**
     * event declenché àvant la creation de l'objet, sert à setter les metadatas /filename etc...
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args) {
        $media = $args->getEntity();
        if ($media instanceof Media )$this->providerFactory->getProvider($media)->prePersist($media);
    }
    
    /**
     * event declenché apres la creation de l'objet, sert à sauver le fichier si uploadé
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args) {
        $media = $args->getEntity();
        if ($media instanceof Media )$this->providerFactory->getProvider($media)->postPersist($media);
    }

    /**
     * declenché à l'update de l'objet, sert à delete l'ancien fichier si yen a un nouveau
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args) {
        $media = $args->getEntity();
        if ($media instanceof Media) $this->providerFactory->getProvider($media)->preUpdate($media);
    }

    /**
     * declenché à l'update de l'objet, sert à delete l'ancien fichier si yen a un nouveau
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args) {
        $media = $args->getEntity();
        if ($media instanceof Media) $this->providerFactory->getProvider($media)->postUpdate($media);
    }


    /**
     * declenché à l'update de l'objet, sert à delete le fichier
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args) {
        $media = $args->getEntity();
        if ($media instanceof Media) $this->providerFactory->getProvider($media)->preRemove($media);
    }

    
    
}
