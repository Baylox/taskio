<?php
namespace App\EntityListener;

use App\Entity\Board;
use App\Entity\Account;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::prePersist, entity: Board::class)]
final class BoardOwnerListener
{
    public function __construct(private Security $security) {}

    public function prePersist(Board $board): void
    {
        if ($board->getOwner()) {
            return; // fixtures/import
        }

        $user = $this->security->getUser();

        if (!$user instanceof Account) {
            throw new \LogicException('Only logged-in accounts can create boards.');
        }

        $board->setOwner($user);
    }
}

