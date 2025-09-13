<?php

namespace App\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Board;
use App\Entity\Lane;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Board::class)]
final class BoardTest extends TestCase
{
    public function testAddLaneKeepsBidirectionalConsistency(): void
    {
        $board = new Board();
        $lane  = new Lane();

        $board->addLane($lane);

        // Lane must know its Board
        $this->assertSame($board, $lane->getBoard());

        $this->assertTrue($board->getLanes()->contains($lane));
        $board->addLane($lane);
        $this->assertCount(1, $board->getLanes());
    }

    public function testRemoveLaneClearsBidirectionalConsistency(): void
    {
        $board = new Board();
        $lane  = new Lane();
        $board->addLane($lane);

        $board->removeLane($lane);

        // Lane is no longer in the Board
        $this->assertFalse($board->getLanes()->contains($lane));
        $this->assertNull($lane->getBoard());
    }
}
