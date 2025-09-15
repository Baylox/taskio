<?php

namespace App\Repository;

use App\Entity\Board;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Account;

/**
 * @extends ServiceEntityRepository<Board>
 */
class BoardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Board::class);
    }


    public function findByAccount(Account $account): array
{
    return $this->createQueryBuilder('b')
        ->innerJoin('b.accounts', 'a')
        ->andWhere('a = :account')
        ->setParameter('account', $account)
        ->orderBy('b.id', 'ASC')
        ->getQuery()
        ->getResult();
}


    //    /**
    //     * @return Board[] Returns an array of Board objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Board
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
