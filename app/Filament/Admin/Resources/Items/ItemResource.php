<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Items;

use App\Enums\AttributeInputType;
use App\Filament\Admin\Resources\Items\Pages\ManageItems;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Item;
use App\Models\Norm;
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
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Item';

    protected static ?string $pluralModelLabel = 'Itens';

    protected static string|\UnitEnum|null $navigationGroup = 'Dados Mestres';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->tabs([
                        Tabs\Tab::make('Dados Básicos')
                            ->schema([
                                Select::make('standard_id')
                                    ->label('Padrão')
                                    ->options(Standard::active()->orderBy('name')->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                        // Pré-seleciona atributos do template do padrão
                                        if (! $state) {
                                            return;
                                        }
                                        $standard = Standard::with('standardAttributes.attribute')->find($state);
                                        if (! $standard) {
                                            return;
                                        }
                                        $attrIds = $standard->standardAttributes->pluck('attribute_id')->toArray();
                                        $set('attributes', $attrIds);
                                    }),

                                Select::make('tag_id')
                                    ->label('Etiqueta')
                                    ->options(fn () => \App\Models\Tag::active()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),

                                TextInput::make('name')
                                    ->label('Nome')
                                    ->required()
                                    ->maxLength(150),

                                Select::make('digit_count')
                                    ->label('Dígitos do código')
                                    ->options([4 => '4 dígitos', 6 => '6 dígitos'])
                                    ->required()
                                    ->default(6),

                                TextInput::make('expiration_months')
                                    ->label('Validade (meses)')
                                    ->numeric()
                                    ->required()
                                    ->default(12)
                                    ->minValue(1),

                                FileUpload::make('photo_path')
                                    ->label('Foto')
                                    ->image()
                                    ->directory('items')
                                    ->nullable(),

                                Toggle::make('is_active')
                                    ->label('Ativo')
                                    ->default(true),
                            ])->columns(2),

                        Tabs\Tab::make('Atributos')
                            ->schema([
                                Placeholder::make('attr_hint')
                                    ->label('')
                                    ->content('Os atributos marcados serão usados no formulário de inspeção de campo. Ao selecionar um Padrão na aba anterior, os atributos do template são pré-selecionados automaticamente.'),

                                CheckboxList::make('attributes')
                                    ->label('Atributos')
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

                        Tabs\Tab::make('Normas')
                            ->schema([
                                CheckboxList::make('norms')
                                    ->label('Normas associadas')
                                    ->relationship('norms', 'name')
                                    ->options(
                                        Norm::active()
                                            ->orderBy('code')
                                            ->get()
                                            ->mapWithKeys(fn ($n) => [$n->id => "[{$n->code}] {$n->name}"])
                                    )
                                    ->columns(1)
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
                TextColumn::make('standard.name')
                    ->label('Padrão')
                    ->sortable()
                    ->badge(),
                TextColumn::make('tag.name')
                    ->label('Etiqueta')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('digit_count')
                    ->label('Dígitos')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('expiration_months')
                    ->label('Validade')
                    ->formatStateUsing(fn (int $state) => "{$state} meses")
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('standard_id')
                    ->label('Padrão')
                    ->options(Standard::active()->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
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
            'index' => ManageItems::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
