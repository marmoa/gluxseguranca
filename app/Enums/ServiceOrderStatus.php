<?php

declare(strict_types=1);

namespace App\Enums;

enum ServiceOrderStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Billed = 'billed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Aberta',
            self::InProgress => 'Em Execução',
            self::Completed => 'Concluída',
            self::Billed => 'Faturada',
            self::Cancelled => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'info',
            self::InProgress => 'warning',
            self::Completed => 'success',
            self::Billed => 'gray',
            self::Cancelled => 'danger',
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Open => in_array($next, [self::InProgress, self::Cancelled]),
            self::InProgress => in_array($next, [self::Completed, self::Cancelled]),
            self::Completed => in_array($next, [self::Billed, self::InProgress]),
            self::Billed => false,
            self::Cancelled => false,
        };
    }
}
