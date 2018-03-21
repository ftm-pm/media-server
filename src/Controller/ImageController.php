<?php

namespace App\Controller;

use App\Entity\Image;
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
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postAction(Request $request): JsonResponse
    {
        $handler = $this->get('app.image_handler');

        return $handler->postImage($request);
    }

    /**
     * @Route("api/images", name="api_image_get_list", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getListAction(Request $request): JsonResponse
    {
        $handler = $this->get('app.image_handler');

        return $handler->getList($request);
    }

    /**
     *
     * @Route("api/images/name/{imageName}", name="api_image_get_name", methods={"GET"})
     * @Route("api/images/{image}", name="api_image_get", methods={"GET"})
     *
     * @param Image $image
     *
     * @return JsonResponse
     */
    public function getAction(Image $image): JsonResponse
    {
        $handler = $this->get('app.image_handler');

        return $handler->get($image);
    }

    /**
     * @Route("api/images/name/{imageName}", name="api_image_delete_name", methods={"DELETE"})
     * @Route("api/images/{image}", name="api_image_delete", methods={"DELETE"})
     *
     * @param Image $image
     *
     * @return JsonResponse
     */
    public function deleteAction(Image $image): JsonResponse
    {
        $handler = $this->get('app.image_handler');

        return $handler->delete($image);
    }

}
