<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Commentary;
use App\Entity\Game;
use App\Entity\User;
use App\Form\BookType;
use App\Form\CommentaryType;
use App\Form\GameType;
use App\Repository\BookRepository;
use App\Repository\CommentaryRepository;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Search\Search;
use App\Search\SearchType;
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
    public function home(GameRepository $gr): Response
    {
        $games = $gr->findtByVisibility(true);
        return $this->render('/pages/games.html.twig', ['games' => $games]);
    }
    /**
     * @Route("/profil",name="profil")
     */
    public function profil(UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $bookCount = $userRepository->countBooksInUserCollection($user->getId());
        $gameCount = $userRepository->countGamesInUserCollection($user->getId());
        return $this->render('/pages/profil.html.twig',['bookCount' => $bookCount,'gameCount' => $gameCount]);
    }
    /**
     * @Route("/admin",name="admin")
     */
    public function admin(GameRepository $gr, BookRepository $br): Response
    {
        $games = $gr->findtByVisibility(false);
        $books = $br->findByVisibility(false);
        $gamesCount = $gr->countByVisibility(false);
        $booksCount = $br->countByVisibility(false);
        return $this->render('/pages/admin.html.twig', ['games' => $games, 'books' => $books, 'gamesCount' => $gamesCount, 'booksCount' => $booksCount]);
    }

    /**
     * @Route("/game/{id}",name="viewGame")
     */
    public function viewGame(int $id, GameRepository $gameRepository): Response
    {

        $game = $gameRepository->find($id);

        if ($game == null) {
            throw new NotFoundHttpException("Jeu Inexistant");
        }
        return $this->render('/pages/jeu.html.twig', ['game' => $game]);
    }

    /**
     * @Route("/new-game",name="addGame")
     */
    public function addGame(Request $request, EntityManagerInterface $em): Response
    {

        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $game->setVisible(false);
            $em->persist($game);
            $em->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('/pages/formjeu.html.twig', ['gameForm' => $form->createView()]);
    }

    /**
     * @Route("/edit-game/{id}",name="editGame")
     */
    public function editGame(int $id, Request $request, GameRepository $gameRepository, EntityManagerInterface $em)
    {
        $game = $gameRepository->find($id);
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);
        $game->getId();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($game);
            $em->flush();
            return $this->redirectToRoute('viewGame', ['id' => $game->getId()]);
        }
        return $this->render('/pages/formjeu.html.twig', ['gameForm' => $form->createView()]);
    }

    /**
     * @Route("/approve-game/{id}",name="approveGame")
     */
    public function approveGame(int $id, GameRepository $gameRepository, EntityManagerInterface $entityManager): Response
    {
        $game = $gameRepository->find($id);
        $game->setVisible(true);
        $entityManager->flush();
        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/remove-game/{id}",name="removeGame")
     */

    public function removeGame(int $id, GameRepository $gameRepository, EntityManagerInterface $entityManager): Response
    {
        $game = $gameRepository->find($id);
        $entityManager->remove($game);
        $entityManager->flush();
        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/book/{id}",name="viewBook")
     */
    public function viewBook(int $id, BookRepository $bookRepository, Request $request, EntityManagerInterface $em): Response
    {
        $book = $bookRepository->find($id);

        if ($book == null) {
            throw new NotFoundHttpException("Livre inexistant");
        }

        $commentary = new Commentary();
        $commentary->setBook($book);
        $commentaryForm = $this->createForm(CommentaryType::class,$commentary);

        $commentaryForm->handleRequest($request);

        if($commentaryForm->isSubmitted() && $commentaryForm->isValid())
        {
            /**
             * @var User $user
             */
            $user = $this->getUser();
            $commentary->setDate(new \DateTime('now'));
            $commentary->setUser($user);
            $em->persist($commentary);
            $em->flush();
            return $this->redirectToRoute('viewBook', ['id'=>$book->getId()]);
        }

        return $this->render('/pages/livre.html.twig', ['book' => $book, 'commentaryForm' => $commentaryForm->createView()]);
    }

    /**
     * @Route("/new-book/{gameId}",name="addBook")
     */
    public function addBook(int $gameId, Request $request, PhotoUploader $photoUploader, GameRepository $gameRepository, EntityManagerInterface $em): Response
    {
        $game = $gameRepository->find($gameId);
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
        return $this->render('/pages/formlivre.html.twig', ['bookForm' => $form->createView()]);
    }

    /**
     * @Route("/editBook/{id}",name="editBook")
     */

    public function editBook(int $id, Request $request, PhotoUploader $photoUploader, BookRepository $bookRepository, EntityManagerInterface $em): Response
    {
        $book = $bookRepository->find($id);
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('viewBook', ['id' => $book->getId()]);
        }
        return $this->render('/pages/formlivre.html.twig', ['bookForm' => $form->createView()]);
    }

    /**
     * @Route("/removeBook/{id}",name="removeBook")
     */
    public function removeBook(int $id, BookRepository $bookRepository, EntityManagerInterface $entityManager): Response
    {

        $book = $bookRepository->find($id);
        $entityManager->remove($book);
        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/approve-book/{id}",name="approveBook")
     */
    public function approveBook(int $id, BookRepository $bookRepository, EntityManagerInterface $entityManager)
    {
        $book = $bookRepository->find($id);
        $book->setVisible(true);
        $entityManager->flush();
        return $this->redirectToRoute('admin');
    }
    /**
     * @Route("/signup",name="inscription")
     */
    public function inscription(): Response
    {
        return $this->render('/pages/inscription.html.twig');
    }

    /**
     * @Route("/search",name="search")
     */

    public function search(Request $request, GameRepository $gameRepository)
    {
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $result = $gameRepository->findBySearch( $search);
            return $this->render('/pages/games.html.twig', ['games' => $result]);
        }
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/remove-comment/{id}/{idBook}",name="removeComment")
     */

    public function removeComment(int $id,int $idBook, CommentaryRepository $commentaryRepository, EntityManagerInterface $em, BookRepository $bookRepository):Response
    {
        $commentary = $commentaryRepository->find($id);
        $book = $bookRepository->find($idBook);
        $em->remove($commentary);
        $em->flush();

        return $this->redirectToRoute('viewBook', ['id' => $book->getId()]);
    }

    /**
     * @Route("/collection",name="collection")
     */
    public function collection(UserRepository $userRepository, BookRepository $bookRepository): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $gamesId = $userRepository->findGamesIdInUserCollection($user->getId());


        $collec = [];

        foreach ($gamesId as $id)
        {
            $collec[]= $bookRepository->findByUserandGame($user->getId(),$id);
        }

        return $this->render('/pages/collection.html.twig',['collec'=>$collec]);
    }

    /**
     * @Route("/addBookCollection/{id}",name="addBookToCollection")
     */

    public function addBookToCollection(int $id, BookRepository $bookRepository, EntityManagerInterface $entityManager): Response
    {
        $book = $bookRepository->find($id);
        /**
         * @var User $user
         */
        $user =$this->getUser();
        $user->addCollection($book);
        $entityManager->flush();
        return $this->redirectToRoute('viewBook', ['id' => $book->getId()]);
    }

    /**
     * @Route("/removeBookCollection/{id}",name="removeBookFromCollection")
     */

    public function removeBookFromCollection (int $id, BookRepository $bookRepository, EntityManagerInterface $entityManager): Response
    {
        $book = $bookRepository->find($id);
        /**
         * @var User $user
         */
        $user =$this->getUser();
        $user->removeCollection($book);
        $entityManager->flush();

        return $this->redirectToRoute('collection');
    }
}