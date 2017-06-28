<?php
/**
 * @author Donjohn
 * @date 29/06/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Controller;


use Gaufrette\File;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FineUploaderController extends Controller
{

    /**
     * use to provider template to fineuploader
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderFineUploaderTemplateAction()
    {
        return $this->render($this->getParameter('donjohn.media.fine_uploader.template'));
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelFineUploaderAction(Request $request)
    {
        $response = new JsonResponse([]);
        $response->headers->set('Vary', 'Accept');
        $response->setData(['success' => true]);

        /** @var \SplFileInfo $file */
        foreach ($this->get('oneup_uploader.orphanage.medias')->getFiles() as $file)
        {
            $fs = new Filesystem();
            try {
                $fs->remove([$file->getRealPath()]);
            } catch (IOException $e) {
                $response->setData(['error' => $this->get('translator')->trans('donjohn.oneup.error.delete', ['%filename%' => $request->query->get('filename')]. ' - '. $e->getMessage(), 'DonjohnMediaBundle')]);
                $response->setStatusCode(500);
            }
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function initFineUploaderAction(Request $request)
    {
        $data=[];
        $response = new JsonResponse([]);
        $response->headers->set('Vary', 'Accept');


        $uploadedFiles = $this->get('oneup_uploader.orphanage.medias')->getFiles();


        /** @var \SplFileInfo $file */
        foreach ($uploadedFiles as $file)  {
            $data[]=['name' => $file->getBasename(), 'uuid' => uniqid(), 'size' => $file->getSize()];
        }
        $response->setData($data);
        return $response;
    }

}
