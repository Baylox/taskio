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

    /**
     * Find boards where the user is a member (via accounts).
     * Does not include boards where the user is only the owner.
     * @param Account $account
     * @return Board[]
     */
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

    /**
     * Find one board with its lanes and cards, ordered by position.
     * @param int $id
     * @return Board|null
     */
    public function findWithLanesAndCards(int $id): ?Board
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.lanes', 'l')->addSelect('l')
            ->leftJoin('l.cards', 'c')->addSelect('c')
            ->andWhere('b.id = :id')->setParameter('id', $id)
            ->orderBy('l.position', 'ASC')
            ->addOrderBy('c.position', 'ASC')
            ->getQuery()->getOneOrNullResult();
    }
    /**
     * Find boards visible to the given user (either owner or member).
     * Replaces findAll() for listing boards.
     * @param Account $user
     * @return Board[]
     */
    public function findVisibleForUser(Account $user): array
    {
        $qb = $this->createQueryBuilder('b');

        return $qb
            ->andWhere($qb->expr()->orX(
                'b.owner = :u',
                'EXISTS (SELECT 1 FROM b.accounts a WHERE a = :u)'
            ))
            ->setParameter('u', $user)
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if a user is a member of a given board without hydrating the collection.
     * @param Board $board
     * @param Account $user
     * @return bool
     */
    public function isBoardMember(Board $board, Account $user): bool
    {
        return (bool) $this->createQueryBuilder('b')
            ->select('1')
            ->innerJoin('b.accounts', 'a')
            ->andWhere('b = :b')
            ->andWhere('a = :u')
            ->setParameter('b', $board)
            ->setParameter('u', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
