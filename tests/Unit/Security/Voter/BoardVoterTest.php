<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Account;
use App\Entity\Board;
use App\Repository\BoardRepository;
use App\Security\Voter\BoardVoter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(BoardVoter::class)]
final class BoardVoterTest extends TestCase
{
    private BoardVoter $voter;
    private BoardRepository $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(BoardRepository::class);
        $this->voter = new BoardVoter($this->repository);
    }

    public static function attributesProvider(): array
    {
        return [
            [BoardVoter::VIEW],
            [BoardVoter::EDIT],
            [BoardVoter::DELETE],
        ];
    }

    // --- Support tests ---

    #[DataProvider('attributesProvider')]
    public function testSupportsValidAttributes(string $attribute): void
    {
        $board = $this->createMock(Board::class);
        $token = $this->createTokenWithUser();

        $result = $this->voter->vote($token, $board, [$attribute]);
        $this->assertNotEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testSupportsInvalidAttribute(): void
    {
        $board = $this->createMock(Board::class);
        $token = $this->createTokenWithUser();

        $result = $this->voter->vote($token, $board, ['INVALID_ATTRIBUTE']);
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testSupportsInvalidSubject(): void
    {
        $token = $this->createTokenWithUser();

        $result = $this->voter->vote($token, new \stdClass(), [BoardVoter::VIEW]);
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    // --- Authorization tests ---

    #[DataProvider('invalidUsersProvider')]
    public function testInvalidUsersDenied($user): void
    {
        $board = $this->createMock(Board::class);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->repository->expects($this->never())->method('isBoardMember');

        $result = $this->voter->vote($token, $board, [BoardVoter::VIEW]);
        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    #[DataProvider('attributesProvider')]
    public function testAdminCanDoEverything(string $attribute): void
    {
        $board = $this->createMock(Board::class);
        $admin = $this->createAccount(1, ['ROLE_ADMIN']);
        $token = $this->createTokenWithRoles($admin, ['ROLE_ADMIN']); // ← FIX: getRoleNames()

        // The repository should not be called for admin
        $this->repository->expects($this->never())->method('isBoardMember');

        $result = $this->voter->vote($token, $board, [$attribute]);
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    #[DataProvider('attributesProvider')]
    public function testOwnerCanDoEverything(string $attribute): void
    {
        $owner = $this->createAccount(10);
        $board = $this->createBoardWithOwner($owner);
        $token = $this->createTokenWithRoles($owner, ['ROLE_USER']);

        // The repository should not be called for the owner
        $this->repository->expects($this->never())->method('isBoardMember');

        $result = $this->voter->vote($token, $board, [$attribute]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testMemberCanViewAndEdit(): void
    {
        $owner = $this->createAccount(1);
        $member = $this->createAccount(2);
        $board = $this->createBoardWithOwner($owner);
        $token = $this->createTokenWithRoles($member, ['ROLE_USER']);

        // The repository must be called with the correct parameters
        $this->repository->expects($this->exactly(3)) // VIEW, EDIT, DELETE
            ->method('isBoardMember')
            ->with($board, $member)
            ->willReturn(true);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $board, [BoardVoter::VIEW]));

        $this->assertEquals(VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $board, [BoardVoter::EDIT]));

        $this->assertEquals(VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $board, [BoardVoter::DELETE]));
    }

    #[DataProvider('attributesProvider')]
    public function testNonMemberDenied(string $attribute): void
    {
        $owner = $this->createAccount(1);
        $nonMember = $this->createAccount(3);
        $board = $this->createBoardWithOwner($owner);
        $token = $this->createTokenWithRoles($nonMember, ['ROLE_USER']);

        $this->repository->expects($this->once())
            ->method('isBoardMember')
            ->with($board, $nonMember)
            ->willReturn(false);

        $result = $this->voter->vote($token, $board, [$attribute]);
        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    // Test edge case: board without owner
    #[DataProvider('attributesProvider')]
    public function testBoardWithoutOwner(string $attribute): void
    {
        $user = $this->createAccount(1);
        $board = $this->createBoardWithOwner(null); // No owner
        $token = $this->createTokenWithRoles($user, ['ROLE_USER']);

        $expectedIsMember = in_array($attribute, [BoardVoter::VIEW, BoardVoter::EDIT]);

        $this->repository->expects($this->once())
            ->method('isBoardMember')
            ->with($board, $user)
            ->willReturn($expectedIsMember);

        $expectedResult = $expectedIsMember ?
            VoterInterface::ACCESS_GRANTED :
            VoterInterface::ACCESS_DENIED;

        $result = $this->voter->vote($token, $board, [$attribute]);
        $this->assertSame($expectedResult, $result);
    }

    // --- Helpers ---

    private function createAccount(int $id, array $roles = ['ROLE_USER']): Account
    {
        $account = $this->createMock(Account::class);
        $account->method('getId')->willReturn($id);
        $account->method('getRoles')->willReturn($roles);
        return $account;
    }

    private function createBoardWithOwner(?Account $owner): Board
    {
        $board = $this->createMock(Board::class);
        $board->method('getOwner')->willReturn($owner);
        return $board;
    }

    private function createTokenWithUser(?Account $user = null): TokenInterface
    {
        return $this->createTokenWithRoles($user ?? $this->createAccount(999), ['ROLE_USER']);
    }

    private function createTokenWithRoles(Account $user, array $roleNames): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $token->method('getRoleNames')->willReturn($roleNames); // ← Important fix
        return $token;
    }

    public static function invalidUsersProvider(): array
    {
        return [
            'anonymous' => [null],
            'non-account' => [new class implements UserInterface {
                public function getRoles(): array { return []; }
                public function eraseCredentials(): void {}
                public function getUserIdentifier(): string { return ''; }
            }]
        ];
    }
}

