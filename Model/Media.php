<?php
namespace Donjohn\MediaBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;




/**
 * description 
 * @author Donjohn
 * Class Article
 * @ORM\MappedSuperclass()
*/


class Media
{
    /**
    * @Groups({"api_output"})
    */
    protected $id;

    use ORMBehaviors\Timestampable\Timestampable;
    
    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"api_output","api_input"})
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Groups({"api_output","api_input"})
     */
    protected $providerName;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"api_output"})
     */
    protected $filename;

    /**
     * @var string old filename to delete old file after update
     */
    protected $oldFilename;

    /**
     * @var array collection of paths
     * @Groups({"api_output"})
     */
    protected $paths=array();

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Groups({"api_output"})
     */
    protected $mimeType;
    
    protected $oldMimeType;
    
    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"api_output","api_input"})
     */
    protected $description;
    
    /**
     * @var array
     * @ORM\Column(type="json_array", nullable=true)
     * @Groups({"api_output"})
     */
    protected $metadata=array();

    /**
     * @var string|File|UploadedFile
     * @Groups({"api_input"})
     */
    protected $binaryContent;

    /**
     * @Assert\IsTrue(message="binaryContent can't be empty or null on creation")
     */
    public function isBinaryContentOnCreation()
    {
        return $this->id ||(!$this->id && $this->binaryContent);
        //id ou (pas id et binary)
    }

    /**
     * return old Media
     * @return Media
     */
    public function getOldMedia()
    {
        $oldMedia = clone $this;
        $oldMedia->setFilename($this->oldFilename);
        $oldMedia->setMimeType($this->oldMimeType);
        return $oldMedia;
    }

    /**
     * init old value
     * @return Media
     */
    public function initOldMedia()
    {
        $this->oldMimeType=$this->mimeType;
        $this->oldFilename=$this->filename;
        return $this;
    }


        /**
     * Get mediaId
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Media
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name ?: '';
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Media
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set metadata
     *
     * @param array $metadata
     * @return Media
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }
    
    /**
     * add metadata
     *
     * @param string $key
     * @param mixed $value
     * @return Media
     */
    public function addMetadata($key, $value)
    {
        $this->metadata[$key] = $value;

        return $this;
    }

    /**
     * Get metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * init l'old media et fous le contenu d'un binaire dans la variable.
     * @param $binaryContent
     * @return $this
     */
    public function setBinaryContent($binaryContent)
    {
        $this->initOldMedia();
        $this->binaryContent = $binaryContent;
        return $this;
    }

    /**
     * @return \SplFileInfo|File|UploadedFile
     */
    public function getBinaryContent()
    {
        return $this->binaryContent;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     * @return Media
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType     *
     *
     * @var string $return quel partie ?
     * @return string
     */
    public function getMimeType($return='full')
    {
        $full = $this->mimeType;
        try {list($provider, $type) = explode('/', $this->mimeType);}
        catch (ContextErrorException $e) {$return = 'full';}

        return ${$return};
    }

    /**
     * get ProviderName
     * @return string
     */
    public function getProvider()
    {
        return $this->getMimeType('provider');
    }

    /**
     * get type
     * @return string
     */
    public function getType()
    {
        return $this->getMimeType('type');
    }
            

    /**
     * Set filename
     *
     * @param string $filename
     * @return Media
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }
    
    /**
     * return old filename for delete/update
     * @return string
     */
    public function getOldFilename()
    {
        return $this->oldFilename;
    }

    /**
     * Set providerName
     *
     * @param string $providerName
     *
     * @return Media
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;

        return $this;
    }

    /**
     * Get providerName
     *
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * @return mixed
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param mixed $paths
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;
    }


    public function getPath($format)
    {
        if (!isset($this->paths[$format])) throw new \RuntimeException('format '.$format.' for media '.$this->getId().' is not defined in config');
        return $this->paths[$format];
    }


}
