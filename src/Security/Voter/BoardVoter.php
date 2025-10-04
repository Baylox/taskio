<?php

namespace App\Security\Voter;

use App\Entity\Board;
use App\Entity\Account;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Repository\BoardRepository;

final class BoardVoter extends Voter
{
    public const VIEW   = 'BOARD_VIEW';
    public const EDIT   = 'BOARD_EDIT';
    public const DELETE = 'BOARD_DELETE';

    public function __construct(private readonly BoardRepository $boardRepository) {}
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof Board;
    }

    /**
     * @param Board $board
     */
    protected function voteOnAttribute(string $attribute, mixed $board, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Account) {
            return false;
        }

        // Admin can do anything
        if (in_array('ROLE_ADMIN', $token->getRoleNames(), true)) {
            return true;
        }

        // Owner can do anything on his board
        if ($board->getOwner()?->getId() === $user->getId()) {
                return true;
            }
        // Check if the user is a member of the board
        $isMember = $this->boardRepository->isBoardMember($board, $user);

        return match ($attribute) {
            self::VIEW   => $isMember,
            self::EDIT   => $isMember,
            self::DELETE => false,
        };
    }
}
