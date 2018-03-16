<?php
/**
 * @author jgn
 * @date 16/03/2018
 * @description For ...
 */

namespace Donjohn\MediaBundle\Model;


interface MediaInterface
{
    /**
     * @return mixed
     */
    public function hasBinaryContentOnCreation();

    /**
     * @return mixed
     */
    public function oldMedia();

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $name
     * @return mixed
     */
    public function setName($name);

    /**
     * @return mixed
     */
    public function getName();

    /**
     * @return mixed
     */
    public function __toString();

    /**
     * @param $description
     * @return mixed
     */
    public function setDescription($description);

    /**
     * @return mixed
     */
    public function getDescription();

    /**
     * @param $metadata
     * @return mixed
     */
    public function setMetadata($metadata);

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function addMetadata($key, $value);

    /**
     * @return mixed
     */
    public function getMetadata();

    /**
     * @param null $binaryContent
     * @return mixed
     */
    public function setBinaryContent($binaryContent=null);

    /**
     * @return mixed
     */
    public function getBinaryContent();

    /**
     * @param $mimeType
     * @return mixed
     */
    public function setMimeType($mimeType);

    /**
     * @return mixed
     */
    public function getMimeType();

    /**
     * @param $filename
     * @return mixed
     */
    public function setFilename($filename);

    /**
     * @return mixed
     */
    public function getFilename();

    /**
     * @param $originalFilename
     * @return mixed
     */
    public function setOriginalFilename($originalFilename);

    /**
     * @return mixed
     */
    public function getOriginalFilename();

    /**
     * @param $providerName
     * @return mixed
     */
    public function setProviderName($providerName);

    /**
     * @return mixed
     */
    public function getProviderName();

    /**
     * @return mixed
     */
    public function getPaths();

    /**
     * @param $paths
     * @return mixed
     */
    public function setPaths($paths);

    /**
     * @return mixed
     */
    public function getMd5();

    /**
     * @param $md5
     * @return mixed
     */
    public function setMd5($md5);
}