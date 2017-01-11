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
        $entityName = $request->query->has('entity') ? $request->query->get('entity') : $this->getParameter('donjohn.media.entities')[0];
        if (!in_array($entityName,$this->getParameter('donjohn.media.entities') )) throw new NotFoundHttpException($entityName .'does not exists');

        if (!$media = $this->get('doctrine.orm.default_entity_manager')->getRepository($entityName)->find($id))
            throw new NotFoundHttpException('media '.$entityName.' '.$id.' cannot be found');

        return $this->get('donjohn.media.provider.factory')->getProvider($media)->getDownloadResponse($media);
    }
}