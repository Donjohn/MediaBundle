<?php
/**
 * Created by PhpStorm.
 * User: tpn
 * Date: 20/04/2017
 * Time: 14:36
 */

namespace Donjohn\MediaBundle\Form\Transformer;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;

class MediaIdTransformer implements DataTransformerInterface
{
    private $em;
    private $classMedia;

    public function __construct(EntityManager $em, $classMedia)
    {
        $this->em = $em;
        $this->classMedia = $classMedia;
    }

    public function reverseTransform($id)
    {
        return $this->em->getRepository($this->classMedia)->findOneBy(array('id' => $id));
    }

    public function transform($media)
    {
        return $media;
    }
}
