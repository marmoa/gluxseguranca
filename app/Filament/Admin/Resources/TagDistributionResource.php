<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TagDistributionResource\Pages;
use App\Models\TagDistribution;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
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
use Filament\Tables\Table;

class TagDistributionResource extends Resource
{
    protected static ?string $model = TagDistribution::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Etiquetas';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Distribuições';

    protected static ?string $modelLabel = 'Distribuição de Etiquetas';

    protected static ?string $pluralModelLabel = 'Distribuições de Etiquetas';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tag_inventory_id')
                ->label('Lote de Etiquetas')
                ->relationship('tagInventory', 'batch_code')
                ->getOptionLabelFromRecordUsing(
                    fn ($record) => "{$record->tag->name} — Lote: {$record->batch_code} (Disponível: {$record->current_quantity})"
                )
                ->searchable()
                ->required(),

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
                ->label('Data/Hora')
                ->required()
                ->default(now()),

            Textarea::make('notes')
                ->label('Observações')
                ->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tagInventory.tag.name')
                    ->label('Etiqueta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tagInventory.batch_code')
                    ->label('Lote')
                    ->placeholder('—'),

                TextColumn::make('recipient.name')
                    ->label('Destinatário')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Qtd.')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('distributed_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
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
            ->defaultSort('distributed_at', 'desc')
            ->emptyStateHeading('Nenhuma distribuição registrada')
            ->emptyStateIcon('heroicon-o-truck');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTagDistributions::route('/'),
            'create' => Pages\CreateTagDistribution::route('/create'),
            'edit'   => Pages\EditTagDistribution::route('/{record}/edit'),
        ];
    }
}
