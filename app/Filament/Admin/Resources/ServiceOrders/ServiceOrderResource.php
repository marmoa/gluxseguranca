<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ServiceOrders;

use App\Enums\ServiceOrderStatus;
use App\Filament\Admin\Resources\ServiceOrders\Pages\CreateServiceOrder;
use App\Filament\Admin\Resources\ServiceOrders\Pages\EditServiceOrder;
use App\Filament\Admin\Resources\ServiceOrders\Pages\ListServiceOrders;
use App\Models\City;
use App\Models\Client;
use App\Models\ClientContract;
use App\Models\Quote;
use App\Models\ServiceOrder;
use App\Models\State;
use App\Models\User;
use App\Services\ServiceOrderLifecycleService;
use App\Services\ServiceOrderNumberService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Ordens de Serviço';

    protected static ?string $modelLabel = 'Ordem de Serviço';

    protected static ?string $pluralModelLabel = 'Ordens de Serviço';

    protected static string|\UnitEnum|null $navigationGroup = 'Operações';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados da OS')
                ->columns(2)
                ->schema([
                    TextInput::make('number')
                        ->label('Número')
                        ->disabled()
                        ->placeholder('Gerado automaticamente')
                        ->columnSpan(1),

                    Select::make('status')
                        ->label('Status')
                        ->options(collect(ServiceOrderStatus::cases())->mapWithKeys(
                            fn (ServiceOrderStatus $s) => [$s->value => $s->label()]
                        ))
                        ->default(ServiceOrderStatus::Open->value)
                        ->required()
                        ->disabled()
                        ->columnSpan(1),

                    Select::make('client_id')
                        ->label('Cliente')
                        ->options(fn () => Client::where('is_active', true)->orderBy('trade_name')->pluck('trade_name', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set): void {
                            $set('client_contract_id', null);
                            $set('quote_id', null);
                        })
                        ->columnSpan(2),

                    Select::make('client_contract_id')
                        ->label('Contrato')
                        ->options(function (Get $get): array {
                            $clientId = $get('client_id');
                            if (! $clientId) {
                                return [];
                            }

                            return ClientContract::where('client_id', $clientId)
                                ->where('is_active', true)
                                ->orderBy('number')
                                ->pluck('number', 'id')
                                ->toArray();
                        })
                        ->placeholder('Selecione o cliente primeiro')
                        ->searchable()
                        ->columnSpan(1),

                    Select::make('quote_id')
                        ->label('Orçamento Vinculado')
                        ->options(function (Get $get): array {
                            $clientId = $get('client_id');
                            if (! $clientId) {
                                return [];
                            }

                            return Quote::where('client_id', $clientId)
                                ->whereIn('status', ['approved'])
                                ->orderBy('number')
                                ->pluck('number', 'id')
                                ->toArray();
                        })
                        ->placeholder('Orçamentos aprovados do cliente')
                        ->searchable()
                        ->columnSpan(1),

                    Select::make('user_id')
                        ->label('Responsável')
                        ->options(fn () => User::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->columnSpan(2),
                ]),

            Section::make('Local do Serviço')
                ->columns(2)
                ->schema([
                    Select::make('state_id')
                        ->label('Estado')
                        ->options(fn () => State::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(fn ($set) => $set('city_id', null))
                        ->columnSpan(1),

                    Select::make('city_id')
                        ->label('Cidade')
                        ->options(function (Get $get): array {
                            $stateId = $get('state_id');
                            if (! $stateId) {
                                return [];
                            }

                            return City::where('state_id', $stateId)->orderBy('name')->pluck('name', 'id')->toArray();
                        })
                        ->placeholder('Selecione o estado primeiro')
                        ->searchable()
                        ->columnSpan(1),

                    TextInput::make('address')
                        ->label('Endereço (logradouro, bairro, CEP)')
                        ->maxLength(255)
                        ->columnSpan(2),
                ]),

            Section::make('Agenda e Condições Ambientais')
                ->columns(4)
                ->schema([
                    DatePicker::make('scheduled_at')
                        ->label('Data Prevista')
                        ->displayFormat('d/m/Y')
                        ->columnSpan(2),

                    TextInput::make('temperature')
                        ->label('Temperatura (°C)')
                        ->numeric()
                        ->minValue(-50)
                        ->maxValue(100)
                        ->step(0.1)
                        ->columnSpan(1),

                    TextInput::make('humidity')
                        ->label('Umidade (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->step(0.1)
                        ->columnSpan(1),
                ]),

            Section::make('Observações')
                ->schema([
                    Textarea::make('notes')
                        ->label('Observações')
                        ->rows(3)
                        ->maxLength(2000),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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

                TextColumn::make('user.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable()
                    ->limit(25),

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
                    ->label('Cidade')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Criada em')
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

                SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->options(fn () => Client::where('is_active', true)->orderBy('trade_name')->pluck('trade_name', 'id'))
                    ->searchable(),

                SelectFilter::make('user_id')
                    ->label('Responsável')
                    ->options(fn () => User::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                TrashedFilter::make(),
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

                Action::make('bill')
                    ->label('Faturar')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->color('gray')
                    ->visible(fn (ServiceOrder $record) => $record->status === ServiceOrderStatus::Completed)
                    ->requiresConfirmation()
                    ->action(function (ServiceOrder $record): void {
                        app(ServiceOrderLifecycleService::class)->bill($record);
                    }),

                Action::make('reopen')
                    ->label('Reabrir')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('info')
                    ->visible(fn (ServiceOrder $record) => $record->status === ServiceOrderStatus::Completed)
                    ->requiresConfirmation()
                    ->action(function (ServiceOrder $record): void {
                        app(ServiceOrderLifecycleService::class)->reopen($record);
                    }),

                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (ServiceOrder $record) => in_array($record->status, [
                        ServiceOrderStatus::Open,
                        ServiceOrderStatus::InProgress,
                    ]))
                    ->requiresConfirmation()
                    ->action(function (ServiceOrder $record): void {
                        app(ServiceOrderLifecycleService::class)->cancel($record);
                    }),

                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
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
            'create' => CreateServiceOrder::route('/create'),
            'edit' => EditServiceOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    /** Gera número da OS antes de criar. */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['number'] = app(ServiceOrderNumberService::class)->generate();

        return $data;
    }
}
