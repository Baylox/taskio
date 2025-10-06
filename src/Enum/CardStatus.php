<?php

namespace App\Enum;

enum CardStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in-progress';
    case REVIEW = 'review';
    case DONE = 'done';
    case BLOCKED = 'blocked';

    public function getLabel(): string
    {
        return match($this) {
            self::TODO => 'To Do',
            self::IN_PROGRESS => 'In Progress',
            self::REVIEW => 'In Review',
            self::DONE => 'Done',
            self::BLOCKED => 'Blocked',
        };
    }

    public function getBadgeClass(): string
    {
        return match($this) {
            self::TODO => 'badge-info',
            self::IN_PROGRESS => 'badge-warning',
            self::REVIEW => 'badge-secondary',
            self::DONE => 'badge-success',
            self::BLOCKED => 'badge-error',
        };
    }

    public function getBorderClass(): string
    {
        return match($this) {
            self::TODO => 'bg-info',
            self::IN_PROGRESS => 'bg-warning',
            self::REVIEW => 'bg-secondary',
            self::DONE => 'bg-success',
            self::BLOCKED => 'bg-error',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::TODO => 'fa-circle',
            self::IN_PROGRESS => 'fa-spinner',
            self::REVIEW => 'fa-eye',
            self::DONE => 'fa-check-circle',
            self::BLOCKED => 'fa-ban',
        };
    }
}
