<?php

namespace App\Tests\Unit\Security;

use App\Entity\Account;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

#[CoversClass(UserChecker::class)]
class UserCheckerTest extends TestCase
{
    private UserChecker $userChecker;

    protected function setUp(): void
    {
        $this->userChecker = new UserChecker();
    }

    public function testCheckPreAuthWithVerifiedAccount(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('isVerified')->willReturn(true);

        $this->userChecker->checkPreAuth($account);

        $this->addToAssertionCount(1);
    }

    public function testCheckPreAuthWithUnverifiedAccountThrowsException(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('isVerified')->willReturn(false);

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('Please verify your email address before logging in. A confirmation email was sent to you during registration.');

        $this->userChecker->checkPreAuth($account);
    }

    public function testCheckPreAuthWithNonAccountUserInterface(): void
    {
        $user = $this->createMock(UserInterface::class);

        $this->userChecker->checkPreAuth($user);

        $this->addToAssertionCount(1);
    }

    public function testCheckPostAuthDoesNothing(): void
    {
        $account = $this->createMock(Account::class);

        $this->userChecker->checkPostAuth($account);

        $this->addToAssertionCount(1);
    }
}
