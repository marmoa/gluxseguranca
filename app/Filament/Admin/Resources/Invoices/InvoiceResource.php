<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Invoices;

use App\Enums\InvoiceStatus;
use App\Filament\Admin\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Admin\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Admin\Resources\Invoices\Pages\ListInvoices;
use App\Models\Invoice;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static string|\UnitEnum|null $navigationGroup = 'Financeiro';

    protected static ?string $navigationLabel = 'Faturas';

    protected static ?string $modelLabel = 'Fatura';

    protected static ?string $pluralModelLabel = 'Faturas';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados da Fatura')
                ->columns(2)
                ->schema([
                    TextInput::make('number')
                        ->label('Número da Fatura')
                        ->placeholder('Ex: FAT-2026-00001')
                        ->maxLength(30)
                        ->required()
                        ->unique(ignoreRecord: true),

                    Select::make('status')
                        ->label('Status')
                        ->options(InvoiceStatus::class)
                        ->required()
                        ->default(InvoiceStatus::Pending)
                        ->native(false),

                    Select::make('client_id')
                        ->label('Cliente')
                        ->relationship('client', 'trade_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('service_order_id')
                        ->label('Ordem de Serviço')
                        ->relationship('serviceOrder', 'number')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->placeholder('Selecione uma OS (opcional)'),
                ]),

            Section::make('Valores')
                ->columns(3)
                ->schema([
                    TextInput::make('amount')
                        ->label('Valor dos Serviços (R$)')
                        ->numeric()
                        ->prefix('R$')
                        ->required()
                        ->minValue(0),

                    TextInput::make('tax_amount')
                        ->label('Impostos / Taxas (R$)')
                        ->numeric()
                        ->prefix('R$')
                        ->default(0)
                        ->minValue(0),

                    TextInput::make('total_amount')
                        ->label('Total (R$)')
                        ->numeric()
                        ->prefix('R$')
                        ->required()
                        ->minValue(0),

                    DatePicker::make('due_date')
                        ->label('Vencimento')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                ]),

            Section::make('Documento PDF')
                ->columns(1)
                ->schema([
                    FileUpload::make('pdf_path')
                        ->label('Arquivo PDF da Fatura')
                        ->disk('public')
                        ->directory('invoices')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)
                        ->downloadable()
                        ->openable()
                        ->nullable(),
                ]),

            Section::make('Observações')
                ->schema([
                    Textarea::make('notes')
                        ->label('Observações Internas')
                        ->rows(3)
                        ->nullable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Nº Fatura')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('serviceOrder.number')
                    ->label('OS')
                    ->searchable()
                    ->sortable()
                    ->prefix('#')
                    ->placeholder('—'),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Invoice $record) => $record->isOverdue() ? 'danger' : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('pdf_path')
                    ->label('PDF')
                    ->formatStateUsing(fn ($state) => $state ? '✔ Disponível' : '—')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(InvoiceStatus::class),
                SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'trade_name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('markSent')
                    ->label('Marcar Enviada')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (Invoice $record) => $record->status === InvoiceStatus::Pending)
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->update([
                            'status'  => InvoiceStatus::Sent,
                            'sent_at' => now(),
                        ]);
                        Notification::make()->title('Fatura marcada como enviada.')->success()->send();
                    }),

                Action::make('markPaid')
                    ->label('Marcar Paga')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Invoice $record) => in_array($record->status, [
                        InvoiceStatus::Pending, InvoiceStatus::Sent, InvoiceStatus::Overdue,
                    ]))
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar pagamento?')
                    ->modalDescription('A fatura será marcada como paga com a data/hora atual.')
                    ->action(function (Invoice $record) {
                        $record->update([
                            'status'  => InvoiceStatus::Paid,
                            'paid_at' => now(),
                        ]);
                        Notification::make()->title('Fatura marcada como paga!')->success()->send();
                    }),

                Action::make('markCancelled')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Invoice $record) => !in_array($record->status, [
                        InvoiceStatus::Paid, InvoiceStatus::Cancelled,
                    ]))
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar fatura?')
                    ->modalDescription('Esta ação não pode ser desfeita.')
                    ->action(function (Invoice $record) {
                        $record->update(['status' => InvoiceStatus::Cancelled]);
                        Notification::make()->title('Fatura cancelada.')->warning()->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'edit'   => EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
