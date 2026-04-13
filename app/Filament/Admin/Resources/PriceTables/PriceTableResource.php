<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PriceTables;

use App\Filament\Admin\Resources\PriceTables\Pages\CreatePriceTable;
use App\Filament\Admin\Resources\PriceTables\Pages\EditPriceTable;
use App\Filament\Admin\Resources\PriceTables\Pages\ListPriceTables;
use App\Filament\Admin\Resources\PriceTables\RelationManagers\ItemsRelationManager;
use App\Models\PriceTable;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PriceTableResource extends Resource
{
    protected static ?string $model = PriceTable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Tabela de Preços';

    protected static ?string $pluralModelLabel = 'Tabelas de Preços';

    protected static string|\UnitEnum|null $navigationGroup = 'Orçamentos';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações da Tabela')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(100),
                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label('Ativa')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('Itens')
                    ->counts('items')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPriceTables::route('/'),
            'create' => CreatePriceTable::route('/create'),
            'edit'   => EditPriceTable::route('/{record}/edit'),
        ];
    }
}
