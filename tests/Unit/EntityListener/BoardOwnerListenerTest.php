<?php
namespace App\Tests\Unit\EntityListener;

use App\Entity\Board;
use App\Entity\Account;
use PHPUnit\Framework\TestCase;
use App\EntityListener\BoardOwnerListener;
use Symfony\Bundle\SecurityBundle\Security;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BoardOwnerListener::class)]
final class BoardOwnerListenerTest extends TestCase
{
    public function testPrePersistSetsOwnerWhenNone(): void
    {
        $board = new Board();

        $user = $this->createMock(Account::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $listener = new BoardOwnerListener($security);
        $listener->prePersist($board);

        $this->assertSame($user, $board->getOwner());
    }

    public function testPrePersistKeepsExistingOwner(): void
    {
        $board = new Board();
        $existingOwner = $this->createMock(Account::class);
        $board->setOwner($existingOwner);

        $security = $this->createMock(Security::class);
        $listener = new BoardOwnerListener($security);

        $listener->prePersist($board);

        $this->assertSame($existingOwner, $board->getOwner());
    }

    public function testPrePersistThrowsWhenNoLoggedUser(): void
    {
        $board = new Board();

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $listener = new BoardOwnerListener($security);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Only logged-in accounts can create boards.');

        $listener->prePersist($board);
    }
}
