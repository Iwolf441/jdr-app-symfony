<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Game;
use App\Form\BookType;
use App\Form\GameType;
use App\Repository\BookRepository;
use App\Repository\GameRepository;
use App\Service\PhotoUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/",name="home")
     */
    public function home( GameRepository $gr): Response {
        $games = $gr->findtByVisibility(true);
        return $this->render('/pages/games.html.twig',['games'=> $games]);
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
    public function admin(GameRepository $gr, BookRepository $br): Response{
        $games = $gr->findtByVisibility(false);
        $books = $br->findByVisibility(false);
        $gamesCount = $gr->countByVisibility(false);
        $booksCount = $br->countByVisibility(false);
        return $this->render('/pages/admin.html.twig',['games'=> $games, 'books'=>$books, 'gamesCount'=> $gamesCount, 'booksCount' => $booksCount]);
    }

    /**
     * @Route("/collection",name="collection")
     */
    public function collection(): Response{
        return $this->render('/pages/collection.html.twig');
    }

    /**
     * @Route("/new-Game",name="addGame")
     */
    public function addGame(Request $request, EntityManagerInterface $em): Response{

        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $game->setVisible(false);
            $em->persist($game);
            $em->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('/pages/formjeu.html.twig',['gameForm'=> $form->createView()]);
    }
    /**
     * @Route("/new-book",name="addBook")
     */
    public function addBook(Request $request, PhotoUploader $photoUploader, EntityManagerInterface $em, GameRepository $gameRepository): Response{
        $game = $gameRepository->find(1);
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $book->setCover($photoUploader->uploadPhoto($form->get('cover')));
            if ($book->getCover() !== null) {
                $em->persist($book->getCover());
            }
            $book->setVisible(false);
            $em->persist($book);
            $game->addBook($book);
            $em->persist($game);
            $em->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('/pages/formlivre.html.twig',['bookForm' => $form->createView()]);
    }

    /**
     * @Route("/game/{id}",name="viewGame")
     */
    public function viewGame(int $id, GameRepository $gameRepository): Response{

        $game = $gameRepository->find($id);

        if ($game == null) {
            throw new NotFoundHttpException("Jeu Inexistant");
        }
        return $this->render('/pages/jeu.html.twig',['game'=> $game]);
    }

    /**
     * @Route("/game/remove/{id}",name="removeGame")
     */

    public function removeGame(int $id, GameRepository $gameRepository, EntityManagerInterface $entityManager)
    {
        $game = $gameRepository->find($id);
        $entityManager->remove($game);
        $entityManager->flush();
        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/book/{id}",name="viewBook")
     */
    public function viewBook(int $id, BookRepository $bookRepository): Response{

        $book = $bookRepository->find($id);
        if ($book == null) {
            throw new NotFoundHttpException("Livre inexistant");
        }
        return $this->render('/pages/livre.html.twig', ['book'=>$book]);
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
