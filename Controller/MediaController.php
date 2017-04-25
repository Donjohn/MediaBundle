<?php
/**
 * @author jgn
 * @date 10/01/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Controller;


use Donjohn\MediaBundle\Form\Type\MediaAddType;
use Donjohn\MediaBundle\Form\Type\MediaType;
use Donjohn\MediaBundle\Model\Media;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaController extends Controller
{

    public function downloadAction(Request $request, $id)
    {
        if (!$media = $this->get('doctrine.orm.default_entity_manager')->getRepository($this->getParameter('donjohn.media.entity'))->find($id))
            throw new NotFoundHttpException('media '.$this->getParameter('donjohn.media.entity').' '.$id.' cannot be found');

        return $this->get('donjohn.media.provider.factory')->getProvider($media)->getDownloadResponse($media);
    }

    public function listAction(Request $request, $page = 1, $maxperpage = 25)
    {
        $form = $this->createForm(MediaType::class, null, array(
                'mediazone' => false,
                'provider' => 'image',
                'allow_delete' => false,
        ));
        $form->add('submit', SubmitType::class, array(
            'label' => 'Envoyer'
        ));
        $form->handleRequest($request);
        if($form->isValid() && $form->isSubmitted())
        {
            $data = $form->getData();
            $this->get('doctrine.orm.default_entity_manager')->persist($data);
            $this->get('doctrine.orm.default_entity_manager')->flush();
        }

        $allmedias = $this->get('doctrine.orm.default_entity_manager')->getRepository($this->getParameter('donjohn.media.entity'))->findAll();
        $medias = $this->get('doctrine.orm.default_entity_manager')->getRepository($this->getParameter('donjohn.media.entity'))->findBy(array(),array('id'=>'DESC'), $maxperpage, $maxperpage*($page-1));

        return $this->render('@DonjohnMedia/Media/list.html.twig',
            array('medias' => $medias,
                'form' => $form->createView(),
                'pagination' => array('page' => $page,
                    'route' => 'donjohn_media_list',
                    'route_params' => $request->attributes->get('_route_params'),
                    'pages_count' => ceil(count($allmedias) / $maxperpage),
                    'attr' => 'data-target=#donjohn-list-modal',
                )
            )
        );

    }

    public function deleteAction(Request $request, Media $media)
    {
        $this->get('doctrine.orm.default_entity_manager')->remove($media);

        return $this->listAction($request);
    }

    public function renderMediaAction($id)
    {
        if (!$media = $this->get('doctrine.orm.default_entity_manager')->getRepository($this->getParameter('donjohn.media.entity'))->find($id))
            throw new NotFoundHttpException('media '.$this->getParameter('donjohn.media.entity').' '.$id.' cannot be found');
        return new Response($this->get('donjohn.media.provider.factory')->getProvider($media)->render($this->get('twig'), $media, array(
            'class' => 'img-rounded visible-xs-inline-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block',
            'filter' => 'thumbnail'
        )));
    }

}
