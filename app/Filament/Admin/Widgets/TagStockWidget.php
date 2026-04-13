<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Models\TagInventory;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TagStockWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $inventories = TagInventory::with('tag')->get();

        $total     = $inventories->sum('current_quantity');
        $lowStock  = $inventories->filter(fn ($i) => $i->isBelowMinimum())->count();
        $types     = $inventories->count();

        $stats = [
            Stat::make('Etiquetas em Estoque', number_format($total))
                ->description("{$types} tipo(s) cadastrado(s)")
                ->color($total > 0 ? 'success' : 'danger')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Lotes com Estoque Baixo', $lowStock)
                ->description($lowStock > 0 ? 'Atenção: reposição necessária' : 'Todos acima do mínimo')
                ->color($lowStock > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
        ];

        // Add individual type details
        foreach ($inventories as $inventory) {
            $stats[] = Stat::make(
                $inventory->tag->name . ' (' . ($inventory->batch_code ?? 'sem lote') . ')',
                number_format($inventory->current_quantity)
            )
                ->description("Mínimo: {$inventory->minimum_stock} | Inicial: {$inventory->initial_quantity}")
                ->color($inventory->isBelowMinimum() ? 'danger' : 'primary');
        }

        return $stats;
    }
}
