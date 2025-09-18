<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Card;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('card')]
final class CardComponent
{
    public Card $card;

    public function title(): string
    {
        return (string) ($this->card->getTitle() ?? '');
    }

    public function hasDescription(): bool
    {
        return null !== $this->card->getDescription() && $this->card->getDescription() !== '';
    }

    public function status(): ?string
    {
        return $this->card->getStatus();
    }
}
