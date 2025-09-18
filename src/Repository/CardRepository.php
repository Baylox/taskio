<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\Lane;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    /**
     * Finds the maximum position value of cards in the given lane.
     * Returns 0 if there are no cards in the lane.
     */
    public function findMaxPositionInLane(Lane $lane): int
    {
        $result = $this->createQueryBuilder('c')
            ->select('MAX(c.position) as maxPosition')
            ->andWhere('c.lane = :lane')
            ->setParameter('lane', $lane)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }

    /**
     * Retrieves the IDs of the cards in a lane, ordered by ascending position.
     *
     * @return int[] Array of card IDs in ascending order by position.
     */
    public function findIdsByLaneOrdered(Lane $lane): array
    {
        $ids = $this->createQueryBuilder('c')
            ->select('c.id')
            ->andWhere('c.lane = :lane')
            ->setParameter('lane', $lane)
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        return array_map('intval', $ids);
    }

    /**
     * After removing a card at $oldPos in $fromLane,
     * close the gap by decrementing positions > oldPos.
     */
    public function compactAfterRemoval(Lane $fromLane, int $oldPos): int
    {
        return $this->_em->createQuery('
            UPDATE App\Entity\Card c
            SET c.position = c.position - 1
            WHERE c.lane = :lane AND c.position > :oldPos
        ')
        ->setParameter('lane', $fromLane)
        ->setParameter('oldPos', $oldPos)
        ->execute();
    }

    /**
    * Before inserting at $newIndex in $toLane,
    * "make room" by incrementing positions >= newIndex.
    */
    public function makeRoomAt(Lane $toLane, int $newIndex): int
    {
        return $this->_em->createQuery('
            UPDATE App\Entity\Card c
            SET c.position = c.position + 1
            WHERE c.lane = :lane AND c.position >= :newIndex
        ')
        ->setParameter('lane', $toLane)
        ->setParameter('newIndex', $newIndex)
        ->execute();
    }
}
