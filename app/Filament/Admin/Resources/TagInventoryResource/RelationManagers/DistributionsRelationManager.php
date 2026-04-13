<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\TagInventoryResource\RelationManagers;

use App\Models\TagInventory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DistributionsRelationManager extends RelationManager
{
    protected static string $relationship = 'distributions';

    protected static ?string $title = 'Distribuições';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('distributed_to')
                ->label('Destinatário (Técnico)')
                ->relationship('recipient', 'name')
                ->searchable()
                ->required(),

            TextInput::make('quantity')
                ->label('Quantidade')
                ->numeric()
                ->required()
                ->minValue(1),

            DateTimePicker::make('distributed_at')
                ->label('Data/Hora da Distribuição')
                ->required()
                ->default(now()),

            Textarea::make('notes')
                ->label('Observações')
                ->rows(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('recipient.name')
                    ->label('Destinatário')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('Quantidade')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('distributed_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Obs.')
                    ->limit(40)
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        return $data;
                    })
                    ->after(function (array $data) {
                        /** @var TagInventory $inventory */
                        $inventory = $this->getOwnerRecord();
                        $qty = (int) $data['quantity'];

                        if ($qty > $inventory->current_quantity) {
                            Notification::make()
                                ->title("Atenção: quantidade distribuída excede o estoque atual ({$inventory->current_quantity}).")
                                ->warning()
                                ->send();
                        } else {
                            $inventory->decrement('current_quantity', $qty);
                        }
                    }),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->defaultSort('distributed_at', 'desc');
    }
}
