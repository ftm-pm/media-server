<?php

namespace App\Controller;

use App\Entity\Image;
use App\Handler\ImageHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ImageController
 * @package App\Controller
 */
class ImageController extends Controller
{
    /**
     * @Route("api/images", name="api_image_post", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postAction(Request $request): JsonResponse
    {
        return $this->getHandler()->postImage($request);
    }

    /**
     * @Route("api/images", name="api_image_get_list", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getListAction(Request $request): JsonResponse
    {
        return $this->getHandler()->getList($request);
    }

    /**
     * @Route("api/images/name/{imageName}", name="api_image_get_name", methods={"GET"})
     * @Route("api/images/{image}", name="api_image_get", methods={"GET"})
     * @param Image $image
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAction(Image $image): JsonResponse
    {
        return $this->getHandler()->get($image);
    }

    /**
     * @Route("api/images/name/{imageName}", name="api_image_delete_name", methods={"DELETE"})
     * @Route("api/images/{image}", name="api_image_delete", methods={"DELETE"})
     * @param Image $image
     * @return JsonResponse
     */
    public function deleteAction(Image $image): JsonResponse
    {
        return $this->getHandler()->delete($image);
    }

    private function getHandler(): ImageHandler {
        return $this->get('App\Handler\ImageHandler');
    }
}
