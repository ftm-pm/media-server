<?php

namespace App\Handler;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class DocumentHandler
 * @package App\Handler
 */
class DocumentHandler
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * DocumentHandler constructor.
     * @param Registry $doctrine
     * @param FormFactory $formFactory
     * @param UploaderHelper $uploaderHelper
     */
    public function __construct(Registry $doctrine, FormFactory $formFactory, UploaderHelper $uploaderHelper)
    {
        $this->doctrine = $doctrine;
        $this->formFactory = $formFactory;
        $this->uploaderHelper = $uploaderHelper;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function postDocument(Request $request): JsonResponse
    {
        $this->check($request);
        $requestData = array_merge(
            $request->request->all(),
            $request->files->all()
        );
        if(!isset($requestData['documentFile'])) {
            throw new InvalidArgumentException('Field documentFile is empty', 500);
        }
        $form = $this->getSubmittedForm($requestData);

        $response = [];
        $status = 200;
        if ($form->isSubmitted() && $form->isValid()) {
            $document = $form->getData();
            $this->saveAndRefreshDocument($document);
            $response = $this->getDocumentResponseData($document);
        } else {
            $response['error'] = $form->getErrors() ?? 'Form is empty or invalid';
            $status = 500;
        }

        return new JsonResponse($response, $status);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getList(Request $request): JsonResponse
    {
        $this->check($request);
        $id = $request->get('id', []);
        $documents = $this->getDocumentsById($id);

        $response = [];
        $status = 200;

        foreach ($documents as $document) {
            $response[] = $this->getDocumentResponseData($document);
        }

        return new JsonResponse($response, $status);
    }

    /**
     * @param Document $document
     * @return JsonResponse
     */
    public function get(Document $document): JsonResponse
    {
        $response = [];
        $status = 404;

        if ($document && $document->getDocumentName()) {
            $response = $this->getDocumentResponseData($document);
            $status = 200;
        }

        return new JsonResponse($response, $status);
    }


    /**
     * @param Document $document
     * @return JsonResponse
     */
    public function delete(Document $document): JsonResponse
    {
        $response = [];
        $status = 400;

        if ($document && $document->getDocumentName()) {
            $em = $this->doctrine->getManager();
            $em->remove($document);
            $em->flush();
            $response = null;
            $status = 204;
        }

        return new JsonResponse($response, $status);
    }

    //////////////////////////////////////

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string|FormTypeInterface $type The built type of the form
     * @param mixed $data The initial data for the form
     * @param array $options Options for the form
     *
     * @return FormInterface
     */
    public function createForm($type, $data = null, array $options = []): FormInterface
    {
        return $this->formFactory->create($type, $data, $options);
    }

    /**
     * @param array $requestData
     * @return FormInterface
     */
    private function getSubmittedForm(array $requestData = []): FormInterface
    {
        $document = new Document();
        $form = $this->createForm('App\Form\Type\DocumentType', $document);
        $form->submit($requestData);

        return $form;
    }

    /**
     * @param Document $document
     */
    private function saveAndRefreshDocument(Document $document): void
    {
        $em = $this->doctrine->getManager();
        $em->persist($document);
        $em->flush();
        $em->refresh($document);
    }

    /**
     * @param Document $document
     * @return string
     */
    private function getOriginalPath(Document $document): string
    {
        return $this->uploaderHelper->asset($document, 'documentFile');
    }

    /**
     * @param array $id
     * @return array
     */
    private function getDocumentsById(array $id = []): array
    {
        if (empty($id)) {
            return [];
        }

        $em = $this->doctrine->getManager();
        $repository = $em->getRepository('App:Document');
        $documents = $repository->findBy([
            'id' => $id
        ]);

        return $documents;
    }

    /**
     * @param Document $document
     * @return array
     */
    private function getDocumentResponseData(Document $document): array
    {
        $data = [
            'id' => $document->getId(),
            'createdAt' => $document->getCreatedAt()->format(\DateTime::ATOM),
            'updatedAt' => $document->getUpdatedAt()->format(\DateTime::ATOM),
            'name' => $document->getDocumentName(),
            'size' => $document->getDocumentSize(),
        ];
        $path = $this->getOriginalPath($document);
        if (!empty($path)) {
            $data['path'] = getenv('APP_HOST') . $path;
        }

        return $data;
    }

    /**
     * @param Request $request
     */
    private function check(Request $request): void
    {
        if ($request->getContentType() === 'json') {
            $request->request->replace(json_decode($request->getContent(), true));
        }
    }
}