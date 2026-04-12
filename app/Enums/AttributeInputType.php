<?php

declare(strict_types=1);

namespace App\Enums;

enum AttributeInputType: string
{
    case Text   = 'text';
    case Select = 'select';

    public function label(): string
    {
        return match ($this) {
            self::Text   => 'Texto livre',
            self::Select => 'Seleção',
        };
    }
}
