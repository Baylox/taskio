<?php

namespace App\Factory;

use App\Entity\Board;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use App\Factory\AccountFactory;

/**
 * @extends PersistentProxyObjectFactory<Board>
 */
final class BoardFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct() {}

    public static function class(): string
    {
        return Board::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array
    {
        return [
            'title' => self::faker()->randomElement([
                'Website Redesign Project',
                'Mobile App Development',
                'Marketing Campaign Q4',
                'Bug Fixes Sprint',
                'Feature Backlog',
                'User Research Board',
                'Team Onboarding',
                'Product Launch',
                'Code Review Tasks',
                'Documentation Updates',
                'Testing & QA',
                'Client Projects',
                'Personal Tasks',
                'Team Meeting Notes',
                'Infrastructure Migration',
            ]),
            'owner' => AccountFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    public function initialize(): static
    {
        return $this->afterInstantiate(function (Board $board): void {
            // Creates 2 new accounts for each board
            $accounts = AccountFactory::new()->many(2)->create();

            foreach ($accounts as $account) {
                $board->addAccount($account->_real());
            }
        });
    }

    public function withAccounts(int $count = 2): self
    {
        return $this->afterInstantiate(function (Board $board) use ($count): void {
            $accounts = AccountFactory::createMany($count);

            foreach ($accounts as $account) {
                $board->addAccount($account->_real());
            }
        });
    }
}
