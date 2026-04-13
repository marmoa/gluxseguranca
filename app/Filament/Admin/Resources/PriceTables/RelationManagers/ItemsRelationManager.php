<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PriceTables\RelationManagers;

use App\Models\Item;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Itens e Preços';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('item_id')
                    ->label('Item')
                    ->options(Item::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->disabledOn('edit'),
                TextInput::make('unit_price')
                    ->label('Preço unitário (R$)')
                    ->numeric()
                    ->prefix('R$')
                    ->required()
                    ->minValue(0),
                Textarea::make('notes')
                    ->label('Observações')
                    ->rows(2)
                    ->nullable()
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('item.standard.name')
                    ->label('Padrão')
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Preço unitário')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Obs.')
                    ->limit(40)
                    ->placeholder('—'),
            ])
            ->defaultSort('item.name')
            ->headerActions([
                CreateAction::make()->label('Adicionar item'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
