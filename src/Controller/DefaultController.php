<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Commentary;
use App\Entity\Game;
use App\Entity\User;
use App\Form\BookFilterType;
use App\Form\BookType;
use App\Form\CommentaryType;
use App\Form\GameType;
use App\Form\UserParameterType;
use App\Form\UserProfilePictureType;
use App\Repository\BookRepository;
use App\Repository\CommentaryRepository;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Search\Filter;
use App\Search\Search;
use App\Search\SearchType;
use App\Service\PhotoUploader;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/",name="home")
     */
    public function home( Request $request,GameRepository $gr, PaginatorInterface $paginator): Response
    {
        $games = $gr->findtByVisibility(true);

        $games =  $paginator->paginate(
            $games, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('/pages/games.html.twig', ['games' => $games]);
    }

    /**
     * @Route("/profil",name="profil")
     */
    public function profil(UserRepository $userRepository, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em, PhotoUploader $photoUploader): Response
    {
        $user = $this->getUser();
        $bookCount = $userRepository->countBooksInUserCollection($user->getId());
        $gameCount = $userRepository->countGamesInUserCollection($user->getId());
        $form = $this->createForm(UserParameterType::class, $user);
        $form2 = $this->createForm(UserProfilePictureType::class,$user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($passwordHasher->isPasswordValid($user, $user->getOldPassword())) {
                $user->setPassword($passwordHasher->hashPassword($user, $user->getPlainpassword()));
                $em->persist($user);
                $em->flush();
                $this->addFlash(
                    'profilChangeSuccess',
                    'Mot de passe changer avec succ??s !'
                );
                return $this->redirectToRoute('profil');
            } else {
                $form->addError(new FormError('Ancien mot de passe incorrect'));
            }
        }

        $form2->handleRequest($request);
        if($form2->isSubmitted() && $form2->isValid())
        {
            $user->setProfilePicture($photoUploader->uploadPhoto($form2->get('profilePicture')));
            if ($user->getProfilePicture() !== null) {
                $em->persist($user->getProfilePicture());
            }
            $em->persist($user);
            $em->flush();
            $this->addFlash(
                'profilChangeSuccess',
                'Photo chang??e avec succ??s !'
            );
            return $this->redirectToRoute('profil');
        }

        return $this->render('/pages/profil.html.twig', ['bookCount' => $bookCount, 'gameCount' => $gameCount, 'parametersForm' => $form->createView(), 'pictureForm'=>$form2->createView()]);
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
    public function viewGame(int $id, GameRepository $gameRepository, Request $request,BookRepository $bookRepository): Response
    {
        $game = $gameRepository->find($id);

        if ($game == null) {
            throw new NotFoundHttpException("Jeu Inexistant");
        } elseif ($game->getVisible() === false) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $books = $game->getBooks();
        $filter =new Filter();

        $form = $this->createForm(BookFilterType::class,$filter);
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            $results= $bookRepository->findByFilter($filter,$game->getId());
            $books= $results;
        }
        return $this->render('/pages/jeu.html.twig', ['game' => $game, 'books' => $books,'filterForm'=> $form->createView()]);
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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $game = $gameRepository->find($id);
        if ($game == null) {
            throw new NotFoundHttpException("Jeu Inexistant");
        } elseif ($game->getVisible() === false) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $game = $gameRepository->find($id);
        if ($game == null) {
            throw new NotFoundHttpException("Jeu Inexistant");
        }
        $game->setVisible(true);
        $entityManager->flush();
        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/remove-game/{id}",name="removeGame")
     */
    public function removeGame(int $id, GameRepository $gameRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $game = $gameRepository->find($id);
        if ($game == null) {
            throw new NotFoundHttpException("Jeu Inexistant");
        }

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
        } elseif ($book->getVisible() === false) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }
        $commentary = new Commentary();
        $commentary->setBook($book);
        $commentaryForm = $this->createForm(CommentaryType::class, $commentary);

        $commentaryForm->handleRequest($request);

        if ($commentaryForm->isSubmitted() && $commentaryForm->isValid()) {
            /**
             * @var User $user
             */
            $user = $this->getUser();
            $commentary->setDate(new \DateTime('now'));
            $commentary->setUser($user);
            $em->persist($commentary);
            $em->flush();
            return $this->redirectToRoute('viewBook', ['id' => $book->getId()]);
        }

        return $this->render('/pages/livre.html.twig', ['book' => $book, 'commentaryForm' => $commentaryForm->createView()]);
    }

    /**
     * @Route("/new-book/{gameId}",name="addBook")
     */
    public function addBook(int $gameId, Request $request, PhotoUploader $photoUploader, GameRepository $gameRepository, EntityManagerInterface $em): Response
    {
        $game = $gameRepository->find($gameId);
        if ($game == null) {
            throw new NotFoundHttpException("Jeu Inexistant");
        }

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
            $this->addFlash(
                'addBookSuccess',
                'Merci pour votre ajout ! Il sera visible lorsqu un admin l aura valid?? !'
            );

            return $this->redirectToRoute('viewGame',['id'=>$gameId]);
        }
        return $this->render('/pages/formlivre.html.twig', ['bookForm' => $form->createView()]);
    }

    /**
     * @Route("/editBook/{id}",name="editBook")
     */
    public function editBook(int $id, Request $request, PhotoUploader $photoUploader, BookRepository $bookRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $book = $bookRepository->find($id);
        if ($book == null) {
            throw new NotFoundHttpException("Livre Inexistant");
        }
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $cover = $photoUploader->uploadPhoto($form->get('cover'));
            if(!$book->getCover() && $cover)
            {
                $book->setCover($cover);
                $em->persist($book->getCover());
            }
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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $book = $bookRepository->find($id);
        if ($book == null) {
            throw new NotFoundHttpException("Livre Inexistant");
        }
        $entityManager->remove($book);
        $entityManager->flush();
        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/approve-book/{id}",name="approveBook")
     */
    public function approveBook(int $id, BookRepository $bookRepository, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $book = $bookRepository->find($id);
        if ($book == null) {
            throw new NotFoundHttpException("Livre Inexistant");
        }

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
    public function search(Request $request, GameRepository $gameRepository, PaginatorInterface $paginator)
    {
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $gameRepository->findBySearch($search);
            $result = $paginator->paginate(
                $result, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                10 /*limit per page*/
            );
            return $this->render('/pages/games.html.twig', ['games' => $result]);
        }
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/remove-comment/{id}/{idBook}",name="removeComment")
     */
    public function removeComment(int $id, int $idBook, CommentaryRepository $commentaryRepository, EntityManagerInterface $em, BookRepository $bookRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $commentary = $commentaryRepository->find($id);
        $book = $bookRepository->find($idBook);
        if ($book || $commentary == null) {
            throw new NotFoundHttpException("Livre ou commentaire Inexistant");
        }
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

        foreach ($gamesId as $id) {
            $collec[] = $bookRepository->findByUserandGame($user->getId(), $id);
        }

        return $this->render('/pages/collection.html.twig', ['collec' => $collec]);
    }

    /**
     * @Route("/addBookCollection/{id}",name="addBookToCollection")
     */
    public function addBookToCollection(int $id, BookRepository $bookRepository, EntityManagerInterface $entityManager): Response
    {
        $book = $bookRepository->find($id);
        if ($book == null) {
            throw new NotFoundHttpException("Livre Inexistant");
        }
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $user->addCollection($book);
        $entityManager->flush();
        return $this->redirectToRoute('viewBook', ['id' => $book->getId()]);
    }
    
    /**
     * @Route("/removeBookCollection/{id}",name="removeBookFromCollection")
     */
    public function removeBookFromCollection(int $id, BookRepository $bookRepository, EntityManagerInterface $entityManager): Response
    {
        $book = $bookRepository->find($id);
        if ($book == null) {
            throw new NotFoundHttpException("Livre Inexistant");
        }
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $user->removeCollection($book);
        $entityManager->flush();

        return $this->redirectToRoute('collection');
    }
}