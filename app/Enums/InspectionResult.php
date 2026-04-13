<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InspectionResult: string implements HasColor, HasLabel
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending  => 'Pendente',
            self::Approved => 'Aprovado',
            self::Rejected => 'Reprovado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending  => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    /** @deprecated Use getLabel() */
    public function label(): string
    {
        return $this->getLabel();
    }

    /** @deprecated Use getColor() */
    public function color(): string
    {
        return $this->getColor();
    }
}
