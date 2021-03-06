<?php

namespace App\Handler;

use App\Entity\Image;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
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
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * ImageHandler constructor.
     * @param Registry $doctrine
     * @param FormFactory $formFactory
     * @param UploaderHelper $uploaderHelper
     * @param CacheManager $cacheManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(Registry $doctrine,
                                FormFactory $formFactory,
                                UploaderHelper $uploaderHelper,
                                CacheManager $cacheManager,
                                TokenStorageInterface $tokenStorage)
    {
        $this->doctrine = $doctrine;
        $this->formFactory = $formFactory;
        $this->uploaderHelper = $uploaderHelper;
        $this->cacheManager = $cacheManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postImage(Request $request): JsonResponse
    {
        $this->check($request);
        $requestData = array_merge(
            $request->request->all(),
            $request->files->all()
        );
        if (!isset($requestData['imageFile'])) {
            throw new InvalidArgumentException('Field imageFile is empty', 500);
        }
        $form = $this->getSubmittedForm($requestData);
        $previews = $request->get('previews', []);
        if (!is_array($previews)) {
            $previews = json_decode($previews, true);
        }
        $response = [];
        $status = 200;
        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->getData();
            $this->saveAndRefreshImage($image);
            $response = $this->getImageResponseData($image, $previews, true);
        } else {
            $response['error'] = $form->getErrors() ?? 'Form is empty or invalid';
            $status = 500;
        }

        return new JsonResponse($response, $status);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getList(Request $request): JsonResponse
    {
        $this->check($request);
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
     * @param Image $image
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(Image $image): JsonResponse
    {
        $response = [];
        $status = 404;

        if ($image && $image->getImageName()) {
            $response = $this->getImageResponseData($image);
            $status = 200;
        }

        return new JsonResponse($response, $status);
    }


    /**
     * @return JsonResponse
     */
    public function delete(Image $image): JsonResponse
    {
        $response = [];
        $status = 400;

        if ($image && $image->getImageName()) {
            $em = $this->doctrine->getManager();
            $em->remove($image);
            $em->flush();
            $response = null;
            $status = 204;
        }

        return new JsonResponse($response, $status);
    }

    /**
     * @param Image $image
     */
    public function setOwner(Image $image): void
    {
        $user = $this->getUser();
        $image->setUser($user);
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
     * @param Image $image
     * @param array $previews
     * @param bool $resolve
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getImagePreviews(Image $image, array $previews = [], bool $resolve = false): array
    {
        $index = 1;
        $client = new \GuzzleHttp\Client();
        foreach ($previews as $key => $preview) {
            $path = $this->cacheManager->getBrowserPath($this->getOriginalPath($image), 'view' . $index, $preview);
            if ($resolve) {
                $client->request('GET', $path);
                $previews[$key] = preg_replace('/\?.*/', '', str_replace('/resolve', '', $path));
            } else {
                $previews[$key] = $path;
            }

            $index++;
        }

        return $previews;
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
     * @param array $previews
     * @param bool $resolve
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getImageResponseData(Image $image, array $previews = [], bool $resolve = false): array
    {
        $data = [
            'id' => $image->getId(),
            'createdAt' => $image->getCreatedAt()->format(\DateTime::ATOM),
            'updatedAt' => $image->getUpdatedAt()->format(\DateTime::ATOM),
            'name' => $image->getImageName(),
            'size' => $image->getImageSize(),
        ];
        $path = $this->getOriginalPath($image);
        if (!empty($path)) {
            $data['origin'] = getenv('APP_HOST') . $path;
        }
        $previews = $this->getImagePreviews($image, $previews, $resolve);
        if (!empty($previews)) {
            $data['previews'] = $previews;
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

    /**
     * @return null|User
     */
    private function getUser(): ?User
    {
        if (!$token = $this->tokenStorage->getToken()) {
            return null;
        }

        $user = $token->getUser();
        return $user instanceof UserInterface ? $user : null;
    }
}