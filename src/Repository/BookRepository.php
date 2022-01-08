<?php

namespace App\Repository;

use App\Entity\Book;
use App\Search\Filter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findByVisibility($visibility)
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.visible = :visibility')
            ->setParameter('visibility', $visibility);

        return $qb->getQuery()->getResult();
    }

    public function countByVisibility($visibility)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.visible = :visibility')
            ->setParameter('visibility', $visibility);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findByUserandGame($idUser, $idGame)
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.users', 'u')
            ->join('b.game', 'g')
            ->where('g.id = :idGame and u.id = :idUser')
            ->setParameters(['idGame' => $idGame, 'idUser' => $idUser]);

        return $qb->getQuery()->getResult();
    }

    public function findByFilter(Filter $filter, $idGame)
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.category','c')
            ->join('b.game','g')
            ->where('g.id = :idGame and c.id = :idCategory')
            ->setParameters(['idGame' => $idGame, 'idCategory'=>$filter->getCategory()->getId()]);

        return $qb->getQuery()->getResult();
    }
}
