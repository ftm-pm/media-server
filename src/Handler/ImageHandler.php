<?php

namespace App\Handler;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class ImageHandler
 * @package App\Handler
 */
class ImageHandler
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
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * ImageHandler constructor.
     * @param Registry $doctrine
     * @param FormFactory $formFactory
     * @param UploaderHelper $uploaderHelper
     * @param CacheManager $cacheManager
     */
    public function __construct(Registry $doctrine, FormFactory $formFactory, UploaderHelper $uploaderHelper, CacheManager $cacheManager)
    {
        $this->doctrine = $doctrine;
        $this->formFactory = $formFactory;
        $this->uploaderHelper = $uploaderHelper;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function postImage(Request $request): JsonResponse
    {
        $requestData = array_merge(
            $request->request->all(),
            $request->files->all()
        );
        $form = $this->getSubmittedForm($requestData);

        $response = [];
        $status = 200;
        if($form->isSubmitted() && $form->isValid()) {
            $image = $form->getData();
            $this->saveAndRefreshImage($image);
            $response = $this->getImageResponseData($image);
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
        $id = $request->get('id', []);
        $images = $this->getImagesById($id);

        $response = [];
        $status = 200;

        foreach ($images as $image) {
            $response[] = $this->getImageResponseData($image);
        }

        return new JsonResponse($response, $status);
    }

    /**
     * @return JsonResponse
     */
    public function get(Image $image): JsonResponse
    {
        $response = [];
        $status = 404;

        if($image &&  $image->getImageName()) {
            $response = $this->getImageResponseData($image);
            $status = 200;
        }

        return new JsonResponse($response, $status);
    }

    //////////////////////////////////////

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string|FormTypeInterface $type    The built type of the form
     * @param mixed                    $data    The initial data for the form
     * @param array                    $options Options for the form
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
        $image = new Image();
        $form = $this->createForm('App\Form\Type\ImageType', $image);
        $form->submit($requestData);

        return $form;
    }

    /**
     * @param Image $image
     */
    private function saveAndRefreshImage(Image $image): void
    {
        $em = $this->doctrine->getManager();
        $em->persist($image);
        $em->flush();
        $em->refresh($image);
    }

    /**
     * @param Image $image
     * @return string
     */
    private function getOriginalPath(Image $image): string
    {
        return $this->uploaderHelper->asset($image, 'imageFile');
    }

    /**
     * TODO: Вернуть список превью
     *
     * @param Image $image
     * @return array
     */
    private function getImagePreviews(Image $image): array
    {
        return [];
    }

    /**
     * @param array $id
     * @return array
     */
    private function getImagesById(array $id = []): array
    {
        if (empty($id)) {
            return [];
        }

        $em = $this->doctrine->getManager();
        $rep = $em->getRepository('App:Image');
        $images = $rep->findBy([
            'id' => $id
        ]);

        return $images;
    }

    /**
     * @param Image $image
     * @return array
     */
    private function getImageResponseData(Image $image): array
    {
        return [
            'path' => $this->getOriginalPath($image),
            'previews' => $this->getImagePreviews($image)
        ];
    }
}