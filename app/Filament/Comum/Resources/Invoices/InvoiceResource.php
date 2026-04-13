<?php

declare(strict_types=1);

namespace App\Filament\Comum\Resources\Invoices;

use App\Enums\InvoiceStatus;
use App\Filament\Comum\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Comum\Resources\Invoices\Pages\ViewInvoice;
use App\Models\Invoice;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static string|\UnitEnum|null $navigationGroup = 'Financeiro';

    protected static ?string $navigationLabel = 'Minhas Faturas';

    protected static ?string $modelLabel = 'Fatura';

    protected static ?string $pluralModelLabel = 'Faturas';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(static::getEloquentQuery())
            ->columns([
                TextColumn::make('number')
                    ->label('Nº Fatura')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('serviceOrder.number')
                    ->label('Ordem de Serviço')
                    ->searchable()
                    ->sortable()
                    ->prefix('#')
                    ->placeholder('—'),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Invoice $record) => $record->isOverdue() ? 'danger' : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('pdf_path')
                    ->label('PDF')
                    ->formatStateUsing(fn ($state) => $state ? '⬇ Baixar PDF' : '—')
                    ->color(fn ($state) => $state ? 'info' : 'gray')
                    ->url(fn (Invoice $record) => $record->hasPdf()
                        ? asset('storage/' . $record->pdf_path)
                        : null
                    )
                    ->openUrlInNewTab(),

                TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(InvoiceStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('due_date', 'desc')
            ->emptyStateHeading('Nenhuma fatura encontrada')
            ->emptyStateDescription('Não há faturas registradas para a sua conta no momento.')
            ->emptyStateIcon('heroicon-o-document-currency-dollar');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'view'  => ViewInvoice::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->with(['serviceOrder', 'client']);

        // Filtrar apenas faturas do cliente logado
        if ($user && $user->client_id) {
            $query->where('client_id', $user->client_id);
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
