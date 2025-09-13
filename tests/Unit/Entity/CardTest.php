<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Card;
use App\Entity\Lane;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

#[CoversClass(Card::class)]
final class CardTest extends TestCase
{
    public function testLaneCanBeAssignedAndRetrieved(): void
    {
        $card = new Card();
        $lane = new Lane();
        $card->setLane($lane);

        self::assertSame($lane, $card->getLane());
    }

    public function testNegativePositionIsRejectedByValidation(): void
    {
        // Requires Assert\PositiveOrZero on Card::$position
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $card = (new Card())->setTitle('A')->setStatus('OPEN')->setPosition(-1);
        self::assertGreaterThan(0, $validator->validate($card)->count());
    }
}
