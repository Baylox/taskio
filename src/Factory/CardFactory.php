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
    protected function defaults(): array|callable
    {
        return [
            'status' => self::faker()->text(24),
            'title' => self::faker()->text(50),
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
