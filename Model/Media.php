<?php
namespace Donjohn\MediaBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * description
 * @author Donjohn
 * Class Article
 * @ORM\MappedSuperclass()
*/


abstract class Media
{
    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $providerName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $filename;

    /**
     * @var string old filename to delete old file after update
     */
    private $oldFilename;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    protected $originalFilename;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $mimeType;

    protected $oldMimeType;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var array
     * @ORM\Column(type="json", nullable=true)
     */
    protected $metadata=array();

    /**
     * @var string|File|UploadedFile
     */
    protected $binaryContent;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $md5;

    /**
     * @Assert\IsTrue(message="media.error.binary_content.empty")
     */
    public function hasBinaryContentOnCreation()
    {
        return $this->getId() ||(!$this->getId()&& $this->getBinaryContent());
    }

    /**
     * return old Media
     * @return Media
     */
    public function oldMedia()
    {
        if ($this->oldFilename) {
            $oldMedia = clone $this;
            $oldMedia->setFilename($this->oldFilename);
            return $oldMedia;
        }
        return null;
    }


        /**
     * Get mediaId
     *
     * @return integer
     */
    abstract public function getId();

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
    public function setBinaryContent($binaryContent=null)
    {
        if (!empty($binaryContent)) {
            $this->oldFilename = $this->filename;
            $this->filename = null;
        } elseif (!empty($this->oldFilename)) {
            $this->filename = $this->oldFilename;
            $this->oldFilename= null;
        }
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
     * @var string
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
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
     * Set originalFilename
     *
     * @param string $originalFilename
     * @return Media
     */
    public function setOriginalFilename($originalFilename)
    {
        $this->originalFilename = $originalFilename;
        return $this;
    }

    /**
     * Get originalFilename
     *
     * @return string
     */
    public function getOriginalFilename()
    {
        return $this->originalFilename;
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
     * @return string
     */
    public function getMd5()
    {
        return $this->md5;
    }

    /**
     * @param string $md5
     */
    public function setMd5($md5)
    {
        $this->md5 = $md5;
    }

    public function getCamelizeName()
    {
        return Container::underscore((new \ReflectionClass($this))->getShortName());
    }
}
