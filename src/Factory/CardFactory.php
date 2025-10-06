<?php

namespace App\Factory;

use App\Entity\Card;
use App\Enum\CardStatus;
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
        $titles = [
            'Fix login bug' => 'Investigate and resolve the issue preventing some users from logging in successfully.',
            'Add user authentication' => 'Implement secure login and registration with hashed passwords and session handling.',
            'Implement search feature' => 'Allow users to search content using keywords and filters for better navigation.',
            'Update documentation' => 'Review and update project documentation to reflect the latest changes.',
            'Code review' => 'Perform a peer review to ensure code quality, maintainability, and adherence to standards.',
            'Database migration' => 'Create and execute migration scripts to update the schema without data loss.',
            'Performance optimization' => 'Identify bottlenecks and optimize queries or code to improve response time.',
            'Security audit' => 'Run a security check for vulnerabilities such as SQL injection or XSS.',
            'UI/UX improvements' => 'Enhance the interface for better usability and user satisfaction.',
            'Test automation setup' => 'Introduce automated tests to reduce regressions and ensure reliability.',
            'API endpoint creation' => 'Develop and document a new API endpoint for client integration.',
            'Bug investigation' => 'Reproduce, analyze, and document a reported bug before fixing it.',
            'Feature specification' => 'Write detailed specs to define requirements and acceptance criteria.',
            'Deploy to production' => 'Prepare release notes, run final checks, and deploy to the live environment.',
            'Refactor legacy code' => 'Clean up outdated code to improve readability and maintainability.',
        ];

        $title = self::faker()->randomElement(array_keys($titles));

        return [
            'status' => self::faker()->randomElement(CardStatus::cases()),
            'title' => $title,
            'description' => self::faker()->optional(0.7)->sentence(). '' .$titles[$title],
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
