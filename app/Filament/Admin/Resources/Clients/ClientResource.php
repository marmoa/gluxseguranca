<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Clients;

use App\Filament\Admin\Resources\Clients\Pages\ManageClients;
use App\Filament\Admin\Resources\Clients\RelationManagers\ContractsRelationManager;
use App\Models\City;
use App\Models\Client;
use App\Models\Standard;
use App\Models\State;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $recordTitleAttribute = 'company_name';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static string|\UnitEnum|null $navigationGroup = 'Dados Mestres';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Empresa')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Razão social')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('trade_name')
                            ->label('Nome fantasia')
                            ->maxLength(150),
                        TextInput::make('cnpj')
                            ->label('CNPJ')
                            ->maxLength(18)
                            ->mask('99.999.999/9999-99')
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone')
                            ->label('Telefone')
                            ->maxLength(20),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(150),
                        Select::make('standard_id')
                            ->label('Padrão de serviço')
                            ->options(Standard::active()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ])->columns(2),

                Section::make('Endereço')
                    ->schema([
                        Select::make('state_id')
                            ->label('Estado')
                            ->options(State::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('city_id', null)),
                        Select::make('city_id')
                            ->label('Cidade')
                            ->options(fn (Get $get) => City::where('state_id', $get('state_id'))
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                        TextInput::make('address')
                            ->label('Endereço')
                            ->maxLength(200),
                        TextInput::make('neighborhood')
                            ->label('Bairro')
                            ->maxLength(100),
                        TextInput::make('zip_code')
                            ->label('CEP')
                            ->maxLength(10)
                            ->mask('99999-999'),
                    ])->columns(2),

                Section::make('Dados Adicionais')
                    ->schema([
                        TextInput::make('segment')
                            ->label('Segmento')
                            ->maxLength(100),
                        TextInput::make('cost_center')
                            ->label('Centro de custo')
                            ->maxLength(50),
                        TextInput::make('base')
                            ->label('Base / Filial')
                            ->maxLength(100),
                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ])->columns(2),

                Section::make('Responsável')
                    ->schema([
                        TextInput::make('contact_name')
                            ->label('Nome')
                            ->maxLength(150),
                        TextInput::make('contact_phone')
                            ->label('Telefone')
                            ->maxLength(20),
                        TextInput::make('contact_mobile')
                            ->label('Celular')
                            ->maxLength(20),
                        TextInput::make('contact_email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(150),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label('Razão social')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trade_name')
                    ->label('Nome fantasia')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('cnpj')
                    ->label('CNPJ')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('state.abbreviation')
                    ->label('UF')
                    ->sortable()
                    ->badge(),
                TextColumn::make('city.name')
                    ->label('Cidade')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('standard.name')
                    ->label('Padrão')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Apenas ativos')
                    ->query(fn (Builder $query) => $query->where('is_active', true))
                    ->default(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('company_name');
    }

    public static function getRelationManagers(): array
    {
        return [
            ContractsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ManageClients::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
