<?php

namespace App\Controller;

use App\Entity\Document;
use App\Handler\DocumentHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DocumentController
 * @package App\Controller
 */
class DocumentController extends Controller
{
    /**
     * @Route("api/documents", name="api_document_post", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function postAction(Request $request): JsonResponse
    {
        return $this->getHandler()->postDocument($request);
    }

    /**
     * @Route("api/documents", name="api_document_get_list", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getListAction(Request $request): JsonResponse
    {
        return $this->getHandler()->getList($request);
    }

    /**
     * @Route("api/documents/name/{documentName}", name="api_document_get_name", methods={"GET"})
     * @Route("api/documents/{document}", name="api_document_get", methods={"GET"})
     * @param Document $document
     * @return JsonResponse
     */
    public function getAction(Document $document): JsonResponse
    {
        return $this->getHandler()->get($document);
    }

    /**
     * @Route("api/documents/name/{documentName}", name="api_document_delete_name", methods={"DELETE"})
     * @Route("api/documents/{document}", name="api_document_delete", methods={"DELETE"})
     * @param Document $document
     * @return JsonResponse
     */
    public function deleteAction(Document $document): JsonResponse
    {
        return $this->getHandler()->delete($document);
    }

    private function getHandler(): DocumentHandler {
        return $this->get('App\Handler\DocumentHandler');
    }
}
