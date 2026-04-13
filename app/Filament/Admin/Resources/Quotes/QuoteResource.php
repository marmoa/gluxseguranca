<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Quotes;

use App\Enums\QuoteStatus;
use App\Filament\Admin\Resources\Quotes\Pages\CreateQuote;
use App\Filament\Admin\Resources\Quotes\Pages\EditQuote;
use App\Filament\Admin\Resources\Quotes\Pages\ListQuotes;
use App\Filament\Admin\Resources\Quotes\RelationManagers\ItemsRelationManager;
use App\Models\Client;
use App\Models\PriceTable;
use App\Models\Quote;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $modelLabel = 'Orçamento';

    protected static ?string $pluralModelLabel = 'Orçamentos';

    protected static string|\UnitEnum|null $navigationGroup = 'Orçamentos';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Orçamento')
                    ->schema([
                        TextInput::make('number')
                            ->label('Número')
                            ->required()
                            ->unique(Quote::class, 'number', ignoreRecord: true)
                            ->default(fn () => 'ORC-' . date('Y') . '-' . str_pad((Quote::withTrashed()->count() + 1), 4, '0', STR_PAD_LEFT))
                            ->maxLength(20),
                        Select::make('client_id')
                            ->label('Cliente')
                            ->options(Client::active()->orderBy('company_name')->pluck('company_name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('price_table_id')
                            ->label('Tabela de preços')
                            ->options(PriceTable::active()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                        Select::make('user_id')
                            ->label('Responsável')
                            ->options(User::active()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->default(fn () => Auth::id())
                            ->nullable(),
                        DatePicker::make('valid_until')
                            ->label('Válido até')
                            ->displayFormat('d/m/Y')
                            ->nullable(),
                        Select::make('status')
                            ->label('Status')
                            ->options(collect(QuoteStatus::cases())->mapWithKeys(
                                fn (QuoteStatus $s) => [$s->value => $s->label()]
                            ))
                            ->default(QuoteStatus::Draft->value)
                            ->required()
                            ->disabled(fn (?Quote $record) => $record !== null),
                    ])->columns(2),

                Section::make('Valores e Observações')
                    ->schema([
                        TextInput::make('total')
                            ->label('Total (R$)')
                            ->prefix('R$')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Calculado automaticamente a partir dos itens'),
                        Textarea::make('notes')
                            ->label('Observações')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.company_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (QuoteStatus $state) => $state->label())
                    ->color(fn (QuoteStatus $state) => $state->color())
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label('Válido até')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (?string $state) => $state && now()->gt($state) ? 'danger' : null),
                TextColumn::make('user.name')
                    ->label('Responsável')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(QuoteStatus::cases())->mapWithKeys(
                        fn (QuoteStatus $s) => [$s->value => $s->label()]
                    )),
                SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->options(Client::active()->orderBy('company_name')->pluck('company_name', 'id'))
                    ->searchable(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('send')
                    ->label('Enviar')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Quote $record) => $record->status === QuoteStatus::Draft)
                    ->action(function (Quote $record): void {
                        $record->update([
                            'status'  => QuoteStatus::Sent,
                            'sent_at' => now(),
                        ]);
                    }),
                Action::make('approve')
                    ->label('Aprovar')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Quote $record) => $record->status === QuoteStatus::Sent)
                    ->action(function (Quote $record): void {
                        $record->update([
                            'status'      => QuoteStatus::Approved,
                            'approved_at' => now(),
                        ]);
                    }),
                Action::make('reject')
                    ->label('Rejeitar')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motivo da rejeição')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn (Quote $record) => $record->status === QuoteStatus::Sent)
                    ->action(function (Quote $record, array $data): void {
                        $record->update([
                            'status'           => QuoteStatus::Rejected,
                            'rejection_reason' => $data['rejection_reason'],
                            'rejected_at'      => now(),
                        ]);
                    }),
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
            'index'  => ListQuotes::route('/'),
            'create' => CreateQuote::route('/create'),
            'edit'   => EditQuote::route('/{record}/edit'),
        ];
    }
}
