<?php

declare(strict_types=1);

namespace App\Filament\Campo\Pages;

use App\Enums\InspectionResult;
use App\Enums\RejectionCategory;
use App\Models\Inspection;
use App\Services\InspectionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class RejectedItems extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'Operações de Campo';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-x-circle';

    protected static ?string $navigationLabel = 'Itens Reprovados';

    protected static ?string $title = 'Itens Reprovados';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.campo.pages.rejected-items';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Inspection::query()
                    ->with(['item', 'serviceOrder.client', 'serviceOrderItem'])
                    ->where('result', InspectionResult::Rejected->value)
                    ->latest()
            )
            ->columns([
                TextColumn::make('traceability_code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('serviceOrder.number')
                    ->label('OS')
                    ->searchable()
                    ->sortable()
                    ->prefix('#'),

                TextColumn::make('serviceOrder.client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('rejection_category')
                    ->label('Categoria')
                    ->formatStateUsing(fn ($state) => $state instanceof RejectionCategory
                        ? $state->getLabel()
                        : ($state ? ucfirst($state) : '—')
                    )
                    ->badge()
                    ->color('danger'),

                TextColumn::make('rejection_notes')
                    ->label('Observações')
                    ->limit(50)
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprovar este item?')
                    ->modalDescription('O item será marcado como aprovado e receberá um código de rastreabilidade.')
                    ->action(function (Inspection $record) {
                        try {
                            /** @var InspectionService $service */
                            $service = app(InspectionService::class);
                            $service->approve($record);

                            Notification::make()
                                ->title("Item aprovado! Código: {$record->fresh()->traceability_code}")
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title($e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->emptyStateHeading('Nenhum item reprovado')
            ->emptyStateDescription('Todos os itens foram aprovados ou ainda não foram inspecionados.')
            ->emptyStateIcon('heroicon-o-check-badge')
            ->defaultSort('created_at', 'desc');
    }
}
