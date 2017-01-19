<?php
/**
 * @author jgn
 * @date 10/01/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaController extends Controller
{

    public function downloadAction(Request $request, $id)
    {
        if (!$media = $this->get('doctrine.orm.default_entity_manager')->getRepository($this->getParameter('donjohn.media.entity'))->find($id))
            throw new NotFoundHttpException('media '.$this->getParameter('donjohn.media.entity').' '.$id.' cannot be found');

        return $this->get('donjohn.media.provider.factory')->getProvider($media)->getDownloadResponse($media);
    }
}