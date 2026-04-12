<?php

declare(strict_types=1);

namespace App\Enums;

enum QuoteStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(): string
    {
        return match($this) {
            self::Draft    => 'Rascunho',
            self::Sent     => 'Enviado',
            self::Approved => 'Aprovado',
            self::Rejected => 'Rejeitado',
            self::Expired  => 'Expirado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft    => 'gray',
            self::Sent     => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Expired  => 'warning',
        };
    }
}
