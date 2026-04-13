<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RejectionCategory: string implements HasLabel
{
    case Visual      = 'visual';
    case Electrical  = 'electrical';
    case Dimensional = 'dimensional';
    case Structural  = 'structural';
    case Other       = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Visual      => 'Visual',
            self::Electrical  => 'Elétrico',
            self::Dimensional => 'Dimensional',
            self::Structural  => 'Estrutural',
            self::Other       => 'Outro',
        };
    }

    /** @deprecated Use getLabel() */
    public function label(): string
    {
        return $this->getLabel();
    }
}
