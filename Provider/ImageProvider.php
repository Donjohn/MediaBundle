<?php

namespace Donjohn\MediaBundle\Provider;

use Donjohn\MediaBundle\Model\Media;
use Gaufrette\Exception\FileNotFound;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * description 
 * @author Donjohn
 */
class ImageProvider extends FileProvider  {
    
    /**
     * @var CacheManager $cacheManager
     */
    protected $cacheManager;
    /**
     * @var FilterConfiguration $filterConfiguration
     */
    protected $filterConfiguration;

    /**
     * @return string alias
     */
    public function getAlias()
    {
        return 'image';
    }

    /**
     * @param CacheManager $cacheManager
     * @required
     */
    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @required
     */
    public function setFilterConfiguration(FilterConfiguration $filterConfiguration)
    {
        $this->filterConfiguration = $filterConfiguration;
    }
    
    /**
     * @inheritdoc
     */
    public function addEditForm(FormBuilderInterface $builder, array $options)
    {
        $options['constraints'] = array(new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => $this->fileMaxSize,
                        'mimeTypes' => $this->getAllowedTypes()
                    ]));
        $builder->add('binaryContent', FileType::class, $options );
    }

    /**
     * @inheritdoc
     */
    public function addCreateForm(FormBuilderInterface $builder, array $options)
    {
        $options['constraints'] = array(new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => $this->fileMaxSize,
                        'mimeTypes' => $this->getAllowedTypes()
                    ]));
        $builder->add('binaryContent', FileType::class, $options );
    }

    /**
     * @inheritdoc
     */
    protected function delete(Media $oMedia, $filter=null)
    {
        try {
            return $this->filesystem->delete($this->getPath($oMedia, $filter));
        } catch (FileNotFound $e) {
            //do nothing, file already deleted
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function postLoad(Media $oMedia)
    {
        $paths = array('reference' => $this->getPath($oMedia));
        foreach ($this->filterConfiguration->all() as $filter=> $configuration) $paths[$filter] = $this->getPath($oMedia, $filter);
        $oMedia->setPaths($paths);
    }

    /**
     * @inheritdoc
     */
    public function preRemove(Media $oMedia)
    {
        $this->delete($oMedia);
        foreach ($this->filterConfiguration->all() as $filter=> $configuration) $this->delete($oMedia, $filter);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPath(Media $oMedia, $filter= null)
    {
        $path = parent::getPath($oMedia);
        return  ($filter && $filter!='reference') ? $this->cacheManager->getBrowserPath($path, $filter) : $path;
    }

    
}
