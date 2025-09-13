<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Hero
{
    public string $title = 'The ultra-fast visual manager';
    public ?string $subtitle = 'Organize your tasks, manage your projects, collaborate as a team.';
    public string $bg = 'assets/images/hero/task-dark-hero-01.svg';
    public ?string $ctaHref = null;
    public ?string $ctaLabel = null;
    public string $size = 'sm';

    public function heightClass(): string
    {
        return match ($this->size) {
            'xs' => 'min-h-[30vh]',
            'sm' => 'min-h-[40vh]',
            'md' => 'min-h-[56vh]',
            'lg' => 'min-h-[70vh]',
            'full' => 'min-h-[90vh]',
            default => 'min-h-[40vh]',
        };
    }
}
