<?php

namespace App\Controller;

use App\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(Request $request)
    {
        $image = new Image();
        $form = $this->createForm('App\Form\Type\ImageType', $image);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $entity = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $rep = $em->getRepository('App:Image');
            $em->persist($entity);
            $em->flush();
            dump('saved!');
        } else {
        }

        return $this->render('default/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}