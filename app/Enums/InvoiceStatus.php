<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum InvoiceStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending   = 'pending';
    case Sent      = 'sent';
    case Overdue   = 'overdue';
    case Paid      = 'paid';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending   => 'Pendente',
            self::Sent      => 'Enviada',
            self::Overdue   => 'Vencida',
            self::Paid      => 'Paga',
            self::Cancelled => 'Cancelada',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending   => 'warning',
            self::Sent      => 'info',
            self::Overdue   => 'danger',
            self::Paid      => 'success',
            self::Cancelled => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending   => 'heroicon-o-clock',
            self::Sent      => 'heroicon-o-paper-airplane',
            self::Overdue   => 'heroicon-o-exclamation-triangle',
            self::Paid      => 'heroicon-o-check-circle',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }
}
