<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ServiceOrderNumber;
use Illuminate\Support\Facades\DB;

class ServiceOrderNumberService
{
    /**
     * Gera o próximo número de OS para o ano corrente.
     * Formato: OS-2026-0001
     * Usa lock para evitar duplicação em concorrência.
     */
    public function generate(): string
    {
        return DB::transaction(function (): string {
            $year = (int) now()->format('Y');

            $row = ServiceOrderNumber::lockForUpdate()->firstOrCreate(
                ['year' => $year],
                ['last_number' => 0, 'prefix' => 'OS'],
            );

            $row->increment('last_number');
            $row->refresh();

            return sprintf('%s-%d-%04d', $row->prefix, $year, $row->last_number);
        });
    }
}
