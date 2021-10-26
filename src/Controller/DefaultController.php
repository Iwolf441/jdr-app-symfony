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
     * @Route("/profil",name="profil")
     */
    public function profil(): Response{
        return $this->render('/pages/profil.html.twig');
    }
    /**
     * @Route("/admin",name="admin")
     */
    public function admin(): Response{
        return $this->render('/pages/admin.html.twig');
    }
    /**
     * @Route("/collection",name="collection")
     */
    public function collection(): Response{
        return $this->render('/pages/collection.html.twig');
    }
    /**
     * @Route("/formJeu",name="formJeu")
     */
    public function formJeu(): Response{
        return $this->render('/pages/formjeu.html.twig');
    }
    /**
     * @Route("/formLivre",name="formLivre")
     */
    public function formLivre(): Response{
        return $this->render('/pages/formlivre.html.twig');
    }
    /**
     * @Route("/jeu",name="jeu")
     */
    public function jeu(): Response{
        return $this->render('/pages/jeu.html.twig');
    }
    /**
     * @Route("/livre",name="livre")
     */
    public function livre(): Response{
        return $this->render('/pages/livre.html.twig');
    }
    /**
     * @Route("/login",name="login")
     */
    public function login(): Response{
        return $this->render('/pages/login.html.twig');
    }
    /**
     * @Route("/signup",name="inscription")
     */
    public function inscription(): Response{
        return $this->render('/pages/inscription.html.twig');
    }
}