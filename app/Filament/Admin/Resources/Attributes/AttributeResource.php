<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Attributes;

use App\Enums\AttributeInputType;
use App\Filament\Admin\Resources\Attributes\Pages\ManageAttributes;
use App\Models\Attribute;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
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

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Atributo';

    protected static ?string $pluralModelLabel = 'Atributos';

    protected static string|\UnitEnum|null $navigationGroup = 'Dados Mestres';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(100),

                Select::make('input_type')
                    ->label('Tipo de entrada')
                    ->options(collect(AttributeInputType::cases())->mapWithKeys(
                        fn (AttributeInputType $type) => [$type->value => $type->label()]
                    ))
                    ->required()
                    ->default(AttributeInputType::Text->value)
                    ->live(),

                Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true),

                Repeater::make('values')
                    ->label('Valores possíveis')
                    ->relationship()
                    ->schema([
                        TextInput::make('value')
                            ->label('Valor')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('sort_order')
                            ->label('Ordem')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(2)
                    ->orderColumn('sort_order')
                    ->addActionLabel('Adicionar valor')
                    ->reorderable()
                    ->collapsible()
                    ->visible(fn (Get $get) => $get('input_type') === AttributeInputType::Select->value),
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
                TextColumn::make('input_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (AttributeInputType $state) => $state->label())
                    ->badge()
                    ->color(fn (AttributeInputType $state) => match ($state) {
                        AttributeInputType::Text   => 'info',
                        AttributeInputType::Select => 'success',
                    })
                    ->sortable(),
                TextColumn::make('values_count')
                    ->label('Valores')
                    ->counts('values')
                    ->sortable(),
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
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAttributes::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
