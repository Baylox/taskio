<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Lane;
use App\Entity\Board;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Validator\Validation;
use PHPUnit\Framework\Attributes\CoversClass;


#[CoversClass(Lane::class)]
final class LaneTest extends TestCase
{
    public function testBoardCanBeAssignedAndRetrieved(): void
    {
        $board = new Board();
        $lane = new Lane();
        $lane->setBoard($board);

        self::assertSame($board, $lane->getBoard());
    }

    public function testNegativePositionIsRejectedByValidation(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $lane = (new Lane())->setTitle('A')->setPosition(-1);

        self::assertGreaterThan(
            0,
            $validator->validate($lane)->count(),
            'Negative position should trigger a validation error'
        );
    }
}
