<?php

declare(strict_types=1);

namespace App\Filament\Campo\Resources\ServiceOrders;

use App\Enums\ServiceOrderStatus;
use App\Filament\Campo\Resources\ServiceOrders\Pages\ListServiceOrders;
use App\Filament\Campo\Resources\ServiceOrders\Pages\ViewServiceOrder;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\ServiceOrderLifecycleService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Ordens de Serviço';

    protected static ?string $modelLabel = 'Ordem de Serviço';

    protected static ?string $pluralModelLabel = 'Ordens de Serviço';

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados da OS')
                ->columns(2)
                ->schema([
                    TextEntry::make('number')->label('Número'),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn (ServiceOrderStatus $state) => $state->label())
                        ->color(fn (ServiceOrderStatus $state) => $state->color()),
                    TextEntry::make('client.trade_name')->label('Cliente')->columnSpan(2),
                    TextEntry::make('contract.number')->label('Contrato'),
                    TextEntry::make('user.name')->label('Responsável'),
                    TextEntry::make('quote.number')->label('Orçamento'),
                    TextEntry::make('scheduled_at')->label('Data Prevista')->date('d/m/Y'),
                ]),

            Section::make('Local do Serviço')
                ->columns(3)
                ->schema([
                    TextEntry::make('state.name')->label('Estado'),
                    TextEntry::make('city.name')->label('Cidade'),
                    TextEntry::make('address')->label('Endereço'),
                ]),

            Section::make('Condições Ambientais')
                ->columns(2)
                ->schema([
                    TextEntry::make('temperature')->label('Temperatura (°C)'),
                    TextEntry::make('humidity')->label('Umidade (%)'),
                ]),

            Section::make('Observações')
                ->schema([
                    TextEntry::make('notes')->label('Observações'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var User $user */
                $user = Auth::user();

                return $query->where('user_id', $user->id);
            })
            ->columns([
                TextColumn::make('number')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.trade_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ServiceOrderStatus $state) => $state->label())
                    ->color(fn (ServiceOrderStatus $state) => $state->color())
                    ->sortable(),

                TextColumn::make('scheduled_at')
                    ->label('Data Prevista')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('city.name')
                    ->label('Cidade'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(ServiceOrderStatus::cases())->mapWithKeys(
                        fn (ServiceOrderStatus $s) => [$s->value => $s->label()]
                    )),
            ])
            ->actions([
                Action::make('start')
                    ->label('Iniciar')
                    ->icon(Heroicon::OutlinedPlay)
                    ->color('warning')
                    ->visible(fn (ServiceOrder $record) => $record->status === ServiceOrderStatus::Open)
                    ->requiresConfirmation()
                    ->action(function (ServiceOrder $record): void {
                        app(ServiceOrderLifecycleService::class)->start($record);
                    }),

                Action::make('complete')
                    ->label('Concluir')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (ServiceOrder $record) => $record->status === ServiceOrderStatus::InProgress)
                    ->requiresConfirmation()
                    ->action(function (ServiceOrder $record): void {
                        app(ServiceOrderLifecycleService::class)->complete($record);
                    }),

                ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceOrders::route('/'),
            'view' => ViewServiceOrder::route('/{record}'),
        ];
    }
}
