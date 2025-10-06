<?php

namespace App\Story;

use App\Factory\BoardFactory;
use App\Factory\LaneFactory;
use App\Factory\CardFactory;
use App\Factory\AccountFactory;
use Zenstruck\Foundry\Story;

final class BoardsLanesCardsStory extends Story
{
    public function build(): void
    {
        // Retrieve all existing accounts (created by UserStory)
        $accounts = AccountFactory::all();

        // Guarantee specific boards for admin and test user
        $admin = AccountFactory::find(['email' => 'admin@example.com']);
        $testUser = AccountFactory::find(['email' => 'user@example.com']);

        // Create admin board
        $adminBoard = BoardFactory::createOne([
            'owner' => $admin,
        ]);
        $this->createLanesAndCards($adminBoard);

        // Create test user board
        $userBoard = BoardFactory::createOne([
            'owner' => $testUser,
        ]);
        $this->createLanesAndCards($userBoard);

        // Create remaining boards with random owners
        for ($i = 0; $i < 10; $i++) {
            $randomOwner = $accounts[array_rand($accounts)];

            $board = BoardFactory::createOne([
                'owner' => $randomOwner,
            ]);

            $this->createLanesAndCards($board);
        }
    }

    private function createLanesAndCards($board): void
    {
        // Available lane titles (each unique)
        $laneTitles = [
            'Backlog',
            'Current Sprint',
            'Development',
            'Code Review',
            'QA Testing',
            'Staging',
            'Deployed',
            'Archive',
        ];

        // Shuffle and pick 3 to 5 unique lanes
        shuffle($laneTitles);
        $laneCount = random_int(3, 5);
        $selectedLaneTitles = array_slice($laneTitles, 0, $laneCount);

        // Create lanes with unique titles
        $lanes = [];
        foreach ($selectedLaneTitles as $laneTitle) {
            $lanes[] = LaneFactory::createOne([
                'board' => $board,
                'title' => $laneTitle,
            ]);
        }

        // Get all available card titles
        $availableTitles = $this->getAvailableTitles();
        shuffle($availableTitles);

        $titleIndex = 0;

        // For each lane, generate between 3 and 7 cards
        foreach ($lanes as $lane) {
            $cardCount = random_int(3, 7);

            for ($i = 0; $i < $cardCount; $i++) {
                // Use next available title, or fallback to random if we run out
                if ($titleIndex < count($availableTitles)) {
                    $title = $availableTitles[$titleIndex];
                    $titleIndex++;
                } else {
                    $title = null; // Let factory choose randomly
                }

                CardFactory::createOne([
                    'lane' => $lane,
                    'title' => $title
                ]);
            }
        }
    }

    private function getAvailableTitles(): array
    {
        return [
            'Fix login bug',
            'Add user authentication',
            'Implement search feature',
            'Update documentation',
            'Code review',
            'Database migration',
            'Performance optimization',
            'Security audit',
            'UI/UX improvements',
            'Test automation setup',
            'API endpoint creation',
            'Bug investigation',
            'Feature specification',
            'Deploy to production',
            'Refactor legacy code',
            'Setup CI/CD pipeline',
            'Create responsive design',
            'Add email notifications',
            'Setup monitoring',
            'Write unit tests',
            'Fix memory leak',
            'Add dark mode',
            'Optimize images',
            'Setup caching',
            'Add file upload',
            'Implement pagination',
            'Setup backup system',
            'Add user roles',
            'Create admin panel',
            'Fix broken links',
            'Add form validation',
            'Setup SSL certificate',
            'Create mobile app',
            'Add social login',
            'Implement webhooks',
            'Setup error tracking',
            'Add content management',
            'Implement chat feature',
            'Setup load balancing',
            'Add export functionality',
            'Create user dashboard',
            'Fix accessibility issues',
            'Add multi-language',
            'Setup analytics',
            'Create API documentation',
            'Add push notifications',
            'Setup container deployment',
            'Add two-factor auth',
            'Create landing page',
            'Add search filters',
            'Setup log rotation',
        ];
    }
}
