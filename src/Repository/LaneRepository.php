<?php

namespace App\Repository;

use App\Entity\Lane;
use App\Entity\Board;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Lane>
 */
class LaneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lane::class);
    }

    /**
     * Returns the next available position for a lane within the specified board.
     *
     * @param Board $board The board entity for which to determine the next lane position.
     * @return int The next position value for a new lane in the specified board.
     */

    public function getNextPositionForBoard(Board $board): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COALESCE(MAX(l.position), 0)')
            ->andWhere('l.board = :board')
            ->setParameter('board', $board)
            ->getQuery()
            ->getSingleScalarResult() + 1;
    }
    //    /**
    //     * @return Lane[] Returns an array of Lane objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Lane
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
