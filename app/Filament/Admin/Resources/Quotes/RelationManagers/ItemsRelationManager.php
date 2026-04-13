<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Quotes\RelationManagers;

use App\Models\Item;
use App\Models\PriceTableItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Itens do Orçamento';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('item_id')
                    ->label('Item')
                    ->options(Item::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, ?int $state): void {
                        if (!$state) {
                            return;
                        }
                        // Pré-preencher o preço da tabela de preços do orçamento, se houver
                        $priceTableId = $this->getOwnerRecord()->price_table_id;
                        if ($priceTableId) {
                            $priceItem = PriceTableItem::where('price_table_id', $priceTableId)
                                ->where('item_id', $state)
                                ->first();
                            if ($priceItem) {
                                $set('unit_price', $priceItem->unit_price);
                            }
                        }
                    }),
                TextInput::make('quantity')
                    ->label('Quantidade')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->default(1)
                    ->required(),
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
                TextColumn::make('quantity')
                    ->label('Qtd.')
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Preço unit.')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Obs.')
                    ->limit(40)
                    ->placeholder('—'),
            ])
            ->defaultSort('item.name')
            ->headerActions([
                CreateAction::make()
                    ->label('Adicionar item')
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
                DeleteAction::make()
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
                ]),
            ]);
    }
}
