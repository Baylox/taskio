<?php

namespace App\Security\Voter;

use App\Entity\Board;
use App\Entity\Account;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class BoardVoter extends Voter
{
    public const VIEW   = 'BOARD_VIEW';
    public const EDIT   = 'BOARD_EDIT';
    public const DELETE = 'BOARD_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof Board;
    }

    protected function voteOnAttribute(string $attribute, mixed $board, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Account) {
            return false;
        }

        // Admin can do anything
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        if (($owner = $board->getOwner()) && $owner->getId() === $user->getId()) {
            return true;
        }
        $isOwner  = $board->getOwner() && $board->getOwner()->getId() === $user->getId();
        $isMember = $board->getAccounts()->exists(fn($k, $m) => $m->getId() === $user->getId());

        return match ($attribute) {
            self::VIEW   => $isOwner || $isMember,
            self::EDIT   => $isOwner || $isMember,
            self::DELETE => $isOwner,
        };
    }
}
