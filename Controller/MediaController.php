<?php
/**
 * @author jgn
 * @date 10/01/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Controller;


use Donjohn\MediaBundle\Form\Type\MediaType;
use Donjohn\MediaBundle\Model\Media;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaController extends Controller
{
    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction($id)
    {
        /** @var Media $media */
        if (!$media = $this->get('doctrine.orm.default_entity_manager')->getRepository($this->getParameter('donjohn.media.entity'))->find($id))
            throw new NotFoundHttpException('media '.$id.' cannot be found');


        return $this->get('donjohn.media.provider.factory')->getProvider($media)->getDownloadResponse($media);
    }

    /**
     * @param Request $request
     * @param $provider
     * @param $formId
     * @param int $page
     * @param int $maxperpage
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function galleryAction(Request $request, $provider, $formId, $page = 1, $maxperpage = 15)
    {
        $form = $this->createForm(MediaType::class, null, array(
                'mediazone' => true,
                'provider' => $provider,
                'allow_delete' => false,
        ));
        $form->add('submit', SubmitType::class, array(
            'label' => 'media.submit',
            'translation_domain' => 'DonjohnMediaBundle'
        ));

        $form->handleRequest($request);
        if($form->isValid() && $form->isSubmitted())
        {
            $mediaUploaded = $form->getData();
            $this->get('doctrine.orm.default_entity_manager')->persist($mediaUploaded);
            $this->get('doctrine.orm.default_entity_manager')->flush();
            $page=1;
        }

        $adapter = new DoctrineORMAdapter(
            $this->get('doctrine.orm.default_entity_manager')->getRepository($this->getParameter('donjohn.media.entity'))
                ->createQueryBuilder("media")
                ->andWhere('media.providerName = :providerName')
                ->setParameter('providerName', $provider)
                ->addOrderBy('media.id', 'DESC')
        );
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxperpage);
        $pagerfanta->setCurrentPage($page);


        return $this->render('@DonjohnMedia/Media/gallery.html.twig',
            array('medias' => $pagerfanta->getCurrentPageResults(),
                'form' => $form->createView(),
                'formId' => $formId,
                'pagination' => $pagerfanta
            )
        );

    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderFormMediaAction(Request $request)
    {
        if (!$media = $this->get('doctrine.orm.default_entity_manager')->getRepository($this->getParameter('donjohn.media.entity'))->find($request->query->get('id')))
            throw new NotFoundHttpException('media '.$this->getParameter('donjohn.media.entity').' '.$request->query->get('id').' cannot be found');

        return $this->render('@DonjohnMedia/Form/media_form_show.html.twig', ['media' => $media]);
    }



}
