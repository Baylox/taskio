<?php

namespace App\Factory;

use App\Entity\Lane;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Lane>
 */
final class LaneFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @inject services if required
     */
    public function __construct() {}

    public static function class(): string
    {
        return Lane::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @return array<string, mixed>|callable<string, mixed> Default values for the Lane entity.
     */
    protected function defaults(): array|callable
    {
        return [
            'title' => static function () {
                $labels = [
                    'Backlog',
                    'To Do',
                    'In Progress',
                    'Blocked',
                    'In Review',
                    'Testing',
                    'Done',
                    'Archived',
                ];
                return self::faker()->randomElement($labels);
            },
            'position' => null,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this->afterInstantiate(function (Lane $lane): void {
            // Respect a provided position
            if ($lane->getPosition() !== null) {
                return;
            }

            $board = $lane->getBoard();
            if (!$board) {
                throw new \LogicException('Always pass the board: LaneFactory::createOne(["board" => $board])');
            }

            // Dense (simple for fixtures):
            $lane->setPosition($board->getLanes()->count() + 1);
        });
    }
}
