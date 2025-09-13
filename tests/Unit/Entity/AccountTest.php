<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Account;
use App\Entity\Board;
use App\Entity\Role;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;


#[CoversClass(Account::class)]
final class AccountTest extends TestCase
{
    public function testUserIdentifierReturnsEmail(): void
    {
        $a = new Account();
        $a->setEmail('user@example.com');

        self::assertSame('user@example.com', $a->getUserIdentifier());
        self::assertSame('user@example.com', $a->getEmail());
    }

    public function testPasswordDefaultsToEmptyString(): void
    {
        $a = new Account();
        self::assertSame('', $a->getPassword());

        $a->setPassword('hash');
        self::assertSame('hash', $a->getPassword());
    }

    public function testGetRolesDefaultsToRoleUser(): void
    {
        $a = new Account();
        $roles = $a->getRoles();

        self::assertContains('ROLE_USER', $roles);
        self::assertCount(1, $roles); // only ROLE_USER
    }

    public function testEmailValidationConstraints(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $account = (new Account())->setEmail('not-an-email');

        // Only validates the property (avoids UniqueEntity, which is a *class* constraint)
        $violations = $validator->validateProperty($account, 'email');

        $this->assertGreaterThan(0, $violations->count());
    }

    public function testAddRemoveBoardNoDuplicates(): void
    {
        $a = new Account();
        $b = new Board();

        $a->addBoard($b);
        $a->addBoard($b); // musn't duplicate the board

        self::assertTrue($a->getBoards()->contains($b));
        self::assertCount(1, $a->getBoards());

        $a->removeBoard($b);
        self::assertFalse($a->getBoards()->contains($b));
        self::assertCount(0, $a->getBoards());
    }

}
