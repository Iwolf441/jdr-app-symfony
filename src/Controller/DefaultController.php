<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/",name="home")
     */
    public function home(): Response {
        return $this->render('/pages/games.html.twig');
    }
    /**
     * @Route("/book",name="viewBook")
     */
    public function viewBook(): Response{
        return new Response("<div>THIS IS THE NEW SHIT </div>");
    }
}