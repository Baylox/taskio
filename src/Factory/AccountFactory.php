<?php

namespace App\Factory;

use App\Entity\Account;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


/**
 * @extends PersistentProxyObjectFactory<Account>
 */
final class AccountFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public static function class(): string
    {
        return Account::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->safeEmail(),
            'password' => 'password',
            'role' => 'ROLE_USER',
            'isVerified' => true,
            'name'       => self::faker()->firstName(),
            'lastname'   => self::faker()->lastName(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this->afterInstantiate(function(Account $account): void {
            if ($account->getPassword()) {
                $account->setPassword(
                    $this->passwordHasher->hashPassword($account, $account->getPassword())
                );
            }
        });
    }
}
