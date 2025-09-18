<?php

declare(strict_types=1);

namespace App\Twig\Components\Kanban;

use App\Entity\Lane;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('lane')]
final class LaneComponent
{
    public Lane $lane;

    /** @return array */
    public function cards(): array
    {
        return $this->lane->getCards()->toArray();
    }

    public function count(): int
    {
        return $this->lane->getCards()->count();
    }

    public function columnId(): int
    {
        return (int) $this->lane->getId();
    }

    public function title(): string
    {
        return (string) ($this->lane->getTitle() ?? '');
    }
}

