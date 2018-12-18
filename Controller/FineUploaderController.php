<?php
/**
 * @author Donjohn
 * @date 29/06/2017
 * @description For ...
 */

namespace Donjohn\MediaBundle\Controller;

use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\File\FilesystemFile;
use Oneup\UploaderBundle\Uploader\Response\FineUploaderResponse;
use Oneup\UploaderBundle\Uploader\Response\ResponseInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Oneup\UploaderBundle\Controller\FineUploaderController as BaseFineUploaderController;

/**
 * Class FineUploaderController.
 */
class FineUploaderController extends BaseFineUploaderController
{
    /**
     * @return JsonResponse
     */
    public function upload(): JsonResponse
    {
        $request = $this->getRequest();

        $response = new FineUploaderResponse();
        $totalParts = $request->get('qqtotalparts', 1);
        $files = $this->getFiles($request->files);
        $chunked = $totalParts > 1;

        foreach ($files as $file) {
            try {
                $chunked ?
                    $this->handleChunkedUpload($file, $response, $request) :
                    $this->handleUpload($file, $response, $request)
                ;
            } catch (UploadException $e) {
                $response->setSuccess(false);
                $response->setError($this->container->get('translator')->trans($e->getMessage(), [], 'OneupUploaderBundle'));

                $this->errorHandler->addException($response, $e);

                // an error happended, return this error message.
                return $this->createSupportedJsonResponse($response->assemble());
            }
        }

        return $this->createSupportedJsonResponse($response->assemble());
    }

    /**
     * @param UploadedFile      $file
     * @param ResponseInterface $response
     * @param Request           $request
     */
    protected function handleChunkedUpload(UploadedFile $file, ResponseInterface $response, Request $request): void
    {
        // get basic container stuff
        $chunkManager = $this->container->get('oneup_uploader.chunk_manager');

        // get information about this chunked request
        [$last, $uuid, $index, $orig] = $this->parseChunkedRequest($request);

        $chunk = $chunkManager->addChunk($uuid, $index, $file, $orig);

        if (null !== $chunk) {
            $this->dispatchChunkEvents($chunk, $response, $request, $last);
        }

        if ($chunkManager->getLoadDistribution()) {
            $chunks = $chunkManager->getChunks($uuid);
            $assembled = $chunkManager->assembleChunks($chunks, true, $last);

            if (null === $chunk) {
                $this->dispatchChunkEvents($assembled, $response, $request, $last);
            }
        }

        // if all chunks collected and stored, proceed
        // with reassembling the parts
        if ($last) {
            if (!$chunkManager->getLoadDistribution()) {
                $chunks = $chunkManager->getChunks($uuid);
                $assembled = $chunkManager->assembleChunks($chunks, true, true);
            }

            $path = $assembled->getPath();

            $this->handleUpload($assembled, $response, $request);

            $chunkManager->cleanup($path);
        }
    }

    /**
     * @param mixed             $file
     * @param ResponseInterface $response
     * @param Request           $request
     */
    protected function handleUpload($file, ResponseInterface $response, Request $request): void
    {
        // wrap the file if it is not done yet which can only happen
        // if it wasn't a chunked upload, in which case it is definitely
        // on the local filesystem.
        if (!($file instanceof FileInterface)) {
            $file = new FilesystemFile($file);
        }
        $this->validate($file, $request, $response);

        $this->dispatchPreUploadEvent($file, $response, $request);

        // no error happend, proceed
        $namer = $this->container->get($this->config['namer']);
        $name = $namer->name($file);

        if (!$request->get('multiple', true)) {
            foreach ($this->storage->getFiles($request->get('form_name', null)) as $oldfile) {
                /* @var \SplFileInfo $file */
                @unlink($oldfile->getRealPath());
            }
        }

        // perform the real upload
        $uploaded = $this->storage->upload($file, $name, $request->get('form_name', null));

        $this->dispatchPostEvents($uploaded, $response, $request);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function cancelFineUploader(Request $request): JsonResponse
    {
        $response = new JsonResponse(['success' => true]);
        $response->headers->set('Vary', 'Accept');

        /** @var \SplFileInfo $file */
        foreach ($this->storage->getFiles($request->get('form_name', null)) as $file) {
            $fs = new Filesystem();
            try {
                $fs->remove([$file->getRealPath()]);
            } catch (IOException $e) {
                $response->setData(['error' => $this->container->get('translator')->trans('media.oneup.error.delete', ['%filename%' => $request->query->get('filename')], 'DonjohnMediaBundle').' - '.$e->getMessage()]);
                $response->setStatusCode(500);
            }
        }

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function initFineUploader(Request $request): JsonResponse
    {
        $request->getSession()->start();

        $uploadedFiles = $this->storage->getFiles($request->get('form_name', null));

        /** @var \SplFileInfo $file */
        $data = [];
        foreach ($uploadedFiles as $file) {
            $data[] = ['name' => $file->getBasename(), 'uuid' => uniqid('', false), 'size' => $file->getSize()];
        }
        $response = new JsonResponse($data);
        $response->headers->set('Vary', 'Accept');

        return $response;
    }
}
