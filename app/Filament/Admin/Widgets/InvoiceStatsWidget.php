<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class InvoiceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['super_admin', 'admin']);
    }

    protected function getStats(): array
    {
        $overdueCount  = Invoice::where('status', InvoiceStatus::Overdue)->count();
        $overdueAmount = Invoice::where('status', InvoiceStatus::Overdue)->sum('total_amount');

        $pendingCount  = Invoice::whereIn('status', [InvoiceStatus::Pending->value, InvoiceStatus::Sent->value])->count();
        $pendingAmount = Invoice::whereIn('status', [InvoiceStatus::Pending->value, InvoiceStatus::Sent->value])->sum('total_amount');

        $dueSoonCount  = Invoice::dueSoon(7)->count();

        $paidThisMonth = Invoice::where('status', InvoiceStatus::Paid)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total_amount');

        return [
            Stat::make('Faturas Vencidas', $overdueCount)
                ->description('R$ ' . number_format($overdueAmount, 2, ',', '.') . ' em aberto')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($overdueCount > 0 ? 'danger' : 'success'),

            Stat::make('A Vencer (pendentes/enviadas)', $pendingCount)
                ->description(
                    $dueSoonCount > 0
                        ? "{$dueSoonCount} vencem nos próximos 7 dias"
                        : 'R$ ' . number_format($pendingAmount, 2, ',', '.') . ' a receber'
                )
                ->icon('heroicon-o-clock')
                ->color($dueSoonCount > 0 ? 'warning' : 'info'),

            Stat::make('Recebido Este Mês', 'R$ ' . number_format($paidThisMonth, 2, ',', '.'))
                ->description(now()->translatedFormat('F Y'))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
