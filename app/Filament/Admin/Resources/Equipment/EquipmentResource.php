<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Equipment;

use App\Filament\Admin\Resources\Equipment\Pages\ManageEquipment;
use App\Models\Equipment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Equipamento';

    protected static ?string $pluralModelLabel = 'Equipamentos';

    protected static string|\UnitEnum|null $navigationGroup = 'Dados Mestres';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('brand')
                            ->label('Marca')
                            ->maxLength(100),
                        TextInput::make('model')
                            ->label('Modelo')
                            ->maxLength(100),
                        TextInput::make('serial_number')
                            ->label('Número de série')
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ])->columns(2),

                Section::make('Calibração')
                    ->schema([
                        TextInput::make('certificate_number')
                            ->label('Número do certificado')
                            ->maxLength(100),
                        DatePicker::make('calibrated_at')
                            ->label('Data de calibração')
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('calibration_due_at')
                            ->label('Vencimento da calibração')
                            ->displayFormat('d/m/Y')
                            ->after('calibrated_at'),
                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
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
                TextColumn::make('brand')
                    ->label('Marca')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('serial_number')
                    ->label('Nº série')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('certificate_number')
                    ->label('Certificado')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('calibration_due_at')
                    ->label('Vencimento calibração')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Equipment $record) => $record->isCalibrationOverdue() ? 'danger' : null),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('overdue')
                    ->label('Calibração vencida')
                    ->query(fn (Builder $query) => $query->overdue()),
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
            ->defaultSort('calibration_due_at');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEquipment::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
