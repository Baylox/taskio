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
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public static function class(): string
    {
        return Account::class;
    }

    /**
     * @return array Default values for the Account entity.
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
     * @return static
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
