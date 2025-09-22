<?php

namespace App\Factory;

use App\Entity\Card;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Card>
 */
final class CardFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct() {}

    public static function class(): string
    {
        return Card::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @return array<string, mixed>|callable<string, mixed> Default values for the Card entity.
     */
    protected function defaults(): array
    {
        return [
            'status' => self::faker()->randomElement(['todo', 'in-progress', 'review', 'done', 'blocked']),
            'title' => self::faker()->randomElement([
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
            ]),
            'position' => null,
        ];
    }


    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this->afterInstantiate(function (Card $card): void {
            if ($card->getPosition() !== null) return;
            $lane = $card->getLane();
            if (!$lane) {
                throw new \LogicException('Always pass the lane: CardFactory::createOne(["lane"=>$lane])');
            }

            // Dense (simple for fixtures):
            $card->setPosition($lane->getCards()->count() + 1);
        });
    }
}
