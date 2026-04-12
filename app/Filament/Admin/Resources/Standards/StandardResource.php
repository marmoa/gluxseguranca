<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Standards;

use App\Filament\Admin\Resources\Standards\Pages\ManageStandards;
use App\Models\Attribute;
use App\Models\Standard;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

class StandardResource extends Resource
{
    protected static ?string $model = Standard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Padrão';

    protected static ?string $pluralModelLabel = 'Padrões';

    protected static string|\UnitEnum|null $navigationGroup = 'Dados Mestres';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->tabs([
                        Tabs\Tab::make('Dados Básicos')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nome')
                                    ->required()
                                    ->maxLength(100),
                                Textarea::make('description')
                                    ->label('Descrição')
                                    ->rows(3)
                                    ->maxLength(500),
                                Toggle::make('is_active')
                                    ->label('Ativo')
                                    ->default(true),
                            ]),

                        Tabs\Tab::make('Template de Atributos')
                            ->schema([
                                CheckboxList::make('attributes')
                                    ->label('Atributos padrão')
                                    ->helperText('Estes atributos serão pré-selecionados automaticamente ao criar um item vinculado a este padrão.')
                                    ->relationship('attributes', 'name')
                                    ->options(
                                        Attribute::active()
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn ($a) => [
                                                $a->id => $a->name . ' (' . $a->input_type->label() . ')',
                                            ])
                                    )
                                    ->columns(2)
                                    ->bulkToggleable(),
                            ]),
                    ])
                    ->columnSpanFull(),
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
                TextColumn::make('attributes_count')
                    ->label('Atributos no template')
                    ->counts('attributes')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('Itens')
                    ->counts('items')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ManageStandards::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
