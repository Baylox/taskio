<?php

namespace App\Story;

use App\Entity\Board;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;
use App\Story\UserStory;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function build(): void
    {
        // Load the user story
        UserStory::load();

        // Load the board story
        BoardStory::load();

        // Load the board lanes story
        BoardLanesStory::load();

        // Load the boards, lanes, and cards story
        BoardsLanesCardsStory::load();
    }
}
