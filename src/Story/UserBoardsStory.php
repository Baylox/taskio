<?php

namespace App\Story;

use App\Factory\AccountFactory;
use App\Factory\BoardFactory;
use Zenstruck\Foundry\Story;

final class UserBoardsStory extends Story
{
    public function build(): void
    {
        // Retrieve all users (excluding admins)
        $users = AccountFactory::findBy(['role' => 'ROLE_USER']);

        // Retrieve all existing boards
        $boards = BoardFactory::all();

        // Add 1 to 3 user collaborators to each board
        foreach ($boards as $board) {
            $collaboratorCount = random_int(1, 3);
            $randomUsers = array_rand($users, min($collaboratorCount, count($users)));

            // If only one user is selected, array_rand returns an int, not an array
            if (!is_array($randomUsers)) {
                $randomUsers = [$randomUsers];
            }

            // Add collaborators to the board (in addition to the owner)
            foreach ($randomUsers as $userIndex) {
                $board->_real()->addAccount($users[$userIndex]->_real());
            }
        }
    }
}
