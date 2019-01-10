<?php

namespace Donjohn\MediaBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * description.
 *
 * @author Donjohn
 * Class Article
 * @ORM\MappedSuperclass
 */
class Media
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
     * @var string|null
     */
    private $oldFilename;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank
     * @Assert\NotNull
     */
    protected $originalFilename;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $mimeType;

    /**
     * @var
     */
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
    protected $metadatas = array();

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
    public function hasBinaryContentOnCreation(): bool
    {
        return $this->getId() || (!$this->getId() && $this->getBinaryContent());
    }

    /**
     * return old Media.
     *
     * @return Media
     */
    public function oldMedia(): Media
    {
        if ($this->oldFilename) {
            $oldMedia = clone $this;
            $oldMedia->setFilename($this->oldFilename);

            return $oldMedia;
        }

        return null;
    }

    /**
     * Get mediaId.
     *
     * @param null $id
     * @return Media
     */
    public function setId($id = null): Media
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get mediaId.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Media
     */
    public function setName(string $name): Media
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name ?: '';
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Media
     */
    public function setDescription(string $description): Media
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param array $metadatas
     *
     * @return Media
     */
    public function setMetadatas(array $metadatas): Media
    {
        $this->metadatas = $metadatas;

        return $this;
    }

    /**
     * add metadata.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return Media
     */
    public function addMetadata(string $key, $value): Media
    {
        $this->metadatas[$key] = $value;

        return $this;
    }

    /**
     * Get metadata.
     *
     * @return array
     */
    public function getMetadatas(): array
    {
        return $this->metadatas;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function getMetadata(string $key)
    {
        return $this->metadatas[$key] ?? null;
    }

    /**
     * init l'old media et fous le contenu d'un binaire dans la variable.
     *
     * @param $binaryContent
     *
     * @return $this
     */
    public function setBinaryContent($binaryContent = null): Media
    {
        if (!empty($binaryContent)) {
            $this->oldFilename = $this->filename;
            $this->filename = null;
        } elseif (null !== $this->oldFilename) {
            $this->filename = $this->oldFilename;
            $this->oldFilename = null;
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
     * Set mimeType.
     *
     * @param string $mimeType
     *
     * @return Media
     */
    public function setMimeType(string $mimeType): Media
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType     *.
     *
     * @var string
     *
     * @return string
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * Set filename.
     *
     * @param string $filename
     *
     * @return Media
     */
    public function setFilename(string $filename): Media
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Set originalFilename.
     *
     * @param string $originalFilename
     *
     * @return Media
     */
    public function setOriginalFilename(string $originalFilename): Media
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    /**
     * Get originalFilename.
     *
     * @return string
     */
    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    /**
     * Set providerName.
     *
     * @param string $providerName
     *
     * @return Media
     */
    public function setProviderName(string $providerName): Media
    {
        $this->providerName = $providerName;

        return $this;
    }

    /**
     * Get providerName.
     *
     * @return string
     */
    public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    /**
     * @return string
     */
    public function getMd5(): ?string
    {
        return $this->md5;
    }

    /**
     * @param string $md5
     *
     * @return Media
     */
    public function setMd5(string $md5): Media
    {
        $this->md5 = $md5;

        return $this;
    }

    /**
     * @return string
     *
     * @throws \ReflectionException
     */
    public function getCamelizeName(): string
    {
        return Container::underscore((new \ReflectionClass($this))->getShortName());
    }


}
