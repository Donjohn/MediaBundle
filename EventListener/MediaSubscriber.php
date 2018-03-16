<?php
namespace Donjohn\MediaBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Donjohn\MediaBundle\Model\MediaInterface;
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
        $oMedia = $args->getEntity();
        if ($oMedia instanceof MediaInterface )$this->providerFactory->getProvider($oMedia)->postLoad($oMedia);
    }

    /**
     * event declenché àvant la creation de l'objet, sert à setter les metadatas /filename etc...
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args) {
        $oMedia = $args->getEntity();
        if ($oMedia instanceof MediaInterface )$this->providerFactory->getProvider($oMedia)->prePersist($oMedia);
    }
    
    /**
     * event declenché apres la creation de l'objet, sert à sauver le fichier si uploadé
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args) {
        $oMedia = $args->getEntity();
        if ($oMedia instanceof MediaInterface )$this->providerFactory->getProvider($oMedia)->postPersist($oMedia);
    }

    /**
     * declenché à l'update de l'objet, sert à delete l'ancien fichier si yen a un nouveau
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args) {
        $oMedia = $args->getEntity();
        if ($oMedia instanceof MediaInterface) $this->providerFactory->getProvider($oMedia)->preUpdate($oMedia);
    }

    /**
     * declenché à l'update de l'objet, sert à delete l'ancien fichier si yen a un nouveau
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args) {
        $oMedia = $args->getEntity();
        if ($oMedia instanceof MediaInterface) $this->providerFactory->getProvider($oMedia)->postUpdate($oMedia);
    }


    /**
     * declenché à l'update de l'objet, sert à delete le fichier
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args) {
        $oMedia = $args->getEntity();
        if ($oMedia instanceof MediaInterface) $this->providerFactory->getProvider($oMedia)->preRemove($oMedia);
    }

    
    
}
