<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\States;

use App\Filament\Admin\Resources\States\Pages\ManageStates;
use App\Models\State;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StateResource extends Resource
{
    protected static ?string $model = State::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Estado';

    protected static ?string $pluralModelLabel = 'Estados';

    protected static string|\UnitEnum|null $navigationGroup = 'Localização';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(100),
                TextInput::make('abbreviation')
                    ->label('UF')
                    ->required()
                    ->maxLength(2)
                    ->minLength(2)
                    ->upperCase()
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('abbreviation')
                    ->label('UF')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cities_count')
                    ->label('Cidades')
                    ->counts('cities')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('abbreviation');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStates::route('/'),
        ];
    }
}
