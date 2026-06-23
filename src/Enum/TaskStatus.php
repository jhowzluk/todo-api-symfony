<?php

declare(strict_types=1);

namespace App\Enum;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::PENDING => in_array($newStatus, [self::IN_PROGRESS, self::CANCELLED], true),
            self::IN_PROGRESS => in_array($newStatus, [self::COMPLETED, self::CANCELLED], true),
            self::COMPLETED, self::CANCELLED => false,
        };
    }
}
