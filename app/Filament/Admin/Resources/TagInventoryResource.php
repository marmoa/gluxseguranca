<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TagInventoryResource\Pages;
use App\Filament\Admin\Resources\TagInventoryResource\RelationManagers;
use App\Models\TagInventory;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TagInventoryResource extends Resource
{
    protected static ?string $model = TagInventory::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Etiquetas';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Estoque de Etiquetas';

    protected static ?string $modelLabel = 'Lote de Etiquetas';

    protected static ?string $pluralModelLabel = 'Estoque de Etiquetas';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados do Lote')
                ->columns(2)
                ->schema([
                    Select::make('tag_id')
                        ->label('Tipo de Etiqueta')
                        ->relationship('tag', 'name')
                        ->searchable()
                        ->required(),

                    TextInput::make('batch_code')
                        ->label('Código do Lote')
                        ->maxLength(50)
                        ->placeholder('Ex: LOTE-2026-001'),

                    TextInput::make('initial_quantity')
                        ->label('Quantidade Inicial')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->live()
                        ->afterStateUpdated(fn ($state, $set) => $set('current_quantity', $state)),

                    TextInput::make('current_quantity')
                        ->label('Quantidade Atual')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    TextInput::make('minimum_stock')
                        ->label('Estoque Mínimo (Alerta)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    TextInput::make('unit_cost')
                        ->label('Custo Unitário (R$)')
                        ->numeric()
                        ->prefix('R$'),

                    DatePicker::make('received_at')
                        ->label('Data de Recebimento')
                        ->required()
                        ->default(now()),

                    Textarea::make('notes')
                        ->label('Observações')
                        ->columnSpanFull()
                        ->rows(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tag.name')
                    ->label('Etiqueta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('batch_code')
                    ->label('Lote')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('initial_quantity')
                    ->label('Inicial')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('current_quantity')
                    ->label('Atual')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn (TagInventory $record) => $record->isBelowMinimum() ? 'danger' : 'success')
                    ->weight('bold'),

                TextColumn::make('minimum_stock')
                    ->label('Mínimo')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('received_at')
                    ->label('Recebido em')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('low_stock')
                    ->label('Estoque Baixo')
                    ->query(fn (Builder $query) => $query->whereColumn('current_quantity', '<=', 'minimum_stock'))
                    ->toggle(),
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
            ->defaultSort('received_at', 'desc')
            ->emptyStateHeading('Nenhum lote registrado')
            ->emptyStateIcon('heroicon-o-archive-box');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DistributionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTagInventories::route('/'),
            'create' => Pages\CreateTagInventory::route('/create'),
            'edit'   => Pages\EditTagInventory::route('/{record}/edit'),
        ];
    }
}
