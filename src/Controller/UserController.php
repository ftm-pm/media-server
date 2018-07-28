<?php

namespace App\Controller;

use App\Handler\UserHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller
 */
class UserController extends Controller
{
    /**
     * @Route("/api/token", methods={"POST"})
     */
    public function getTokenAction()
    {
        // The security layer will intercept this request
        return new Response('', 401);
    }

    /**
     * @Route("/api/token/confirmation", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function confirmationTokenAction(Request $request)
    {
        $token = $request->get('token', null);
        $response = $this->getHandler()->confirmationEmail($token);

        return new JsonResponse($response, 200);
    }

    /**
     * @Route("/api/register")
     * @param Request $request
     * @return JsonResponse
     */
    public function registerAction(Request $request)
    {
        if ($request->getContentType() === 'json') {
            $request->request->replace(json_decode($request->getContent(), true));
        }
        $response = $this->getHandler()->register($request->request->all());

        return new JsonResponse(['message'=> $response['message']], $response['code']);
    }

    private function getHandler(): UserHandler {
        return $this->get('App\Handler\UserHandler');
    }
}