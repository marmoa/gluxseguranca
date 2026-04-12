<?php

declare(strict_types=1);

namespace App\Filament\Comum\Resources\ServiceOrders;

use App\Enums\ServiceOrderStatus;
use App\Filament\Comum\Resources\ServiceOrders\Pages\ListServiceOrders;
use App\Filament\Comum\Resources\ServiceOrders\Pages\ViewServiceOrder;
use App\Models\ServiceOrder;
use App\Models\User;
use BackedEnum;
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
                    TextEntry::make('user.name')->label('Responsável'),
                    TextEntry::make('scheduled_at')->label('Data Prevista')->date('d/m/Y'),
                    TextEntry::make('started_at')->label('Iniciada em')->dateTime('d/m/Y H:i'),
                    TextEntry::make('completed_at')->label('Concluída em')->dateTime('d/m/Y H:i'),
                ]),

            Section::make('Local')
                ->columns(2)
                ->schema([
                    TextEntry::make('state.name')->label('Estado'),
                    TextEntry::make('city.name')->label('Cidade'),
                    TextEntry::make('address')->label('Endereço')->columnSpan(2),
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

                return $query->where('client_id', $user->client_id);
            })
            ->columns([
                TextColumn::make('number')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ServiceOrderStatus $state) => $state->label())
                    ->color(fn (ServiceOrderStatus $state) => $state->color())
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Responsável')
                    ->sortable()
                    ->limit(25),

                TextColumn::make('scheduled_at')
                    ->label('Data Prevista')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('city.name')
                    ->label('Cidade'),

                TextColumn::make('completed_at')
                    ->label('Concluída em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(ServiceOrderStatus::cases())->mapWithKeys(
                        fn (ServiceOrderStatus $s) => [$s->value => $s->label()]
                    )),
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
