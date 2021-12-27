<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function countBooksInUserCollection($userId)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(c.id)')
            ->join('u.collection','c')
            ->where('u.id= :userId')
            ->setParameter('userId',$userId);
            return $qb->getQuery()->getSingleScalarResult();
    }

    public function countGamesInUserCollection($userId)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(distinct(g.id))')
            ->join('u.collection','c')
            ->join('c.game','g')
            ->where('u.id= :userId')
            ->setParameter('userId',$userId);
        return $qb->getQuery()->getSingleScalarResult();
    }

}
