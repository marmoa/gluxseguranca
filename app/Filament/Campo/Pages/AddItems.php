<?php

declare(strict_types=1);

namespace App\Filament\Campo\Pages;

use App\Models\Item;
use App\Models\ServiceOrder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

class AddItems extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\UnitEnum|null $navigationGroup = 'Operações de Campo';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?string $navigationLabel = 'Adicionar Itens';

    protected static ?string $title = 'Adicionar Itens à OS';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.campo.pages.add-items';

    /** Estado do formulário */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'service_order_id' => Session::get('campo.service_order_id'),
            'item_id'          => null,
            'quantity'         => 1,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('service_order_id')
                    ->label('Ordem de Serviço')
                    ->options(
                        ServiceOrder::whereIn('status', ['open', 'in_progress'])
                            ->with('client')
                            ->get()
                            ->mapWithKeys(fn ($os) => [
                                $os->id => "#{$os->number} — {$os->client?->name}",
                            ])
                    )
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        Session::put('campo.service_order_id', $state);
                    }),

                Select::make('item_id')
                    ->label('Item')
                    ->options(function (Get $get) {
                        $osId = $get('service_order_id');
                        if (! $osId) {
                            return [];
                        }

                        return Item::active()
                            ->with('standard')
                            ->get()
                            ->mapWithKeys(fn ($item) => [
                                $item->id => "{$item->name} ({$item->standard?->name})",
                            ]);
                    })
                    ->searchable()
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('service_order_id'))
                    ->live(),

                TextInput::make('quantity')
                    ->label('Quantidade')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100)
                    ->required()
                    ->default(1),
            ])
            ->statePath('data');
    }

    public function addItem(): void
    {
        $data = $this->form->getState();

        $serviceOrderId = (int) ($data['service_order_id'] ?? 0);
        $itemId         = (int) ($data['item_id'] ?? 0);
        $quantity       = (int) ($data['quantity'] ?? 1);

        if (! $serviceOrderId || ! $itemId || $quantity < 1) {
            Notification::make()
                ->title('Preencha todos os campos corretamente.')
                ->danger()
                ->send();
            return;
        }

        Session::put('campo.service_order_id', $serviceOrderId);

        $items   = Session::get('campo.items', []);
        $items[] = [
            'service_order_id' => $serviceOrderId,
            'item_id'          => $itemId,
            'quantity'         => $quantity,
        ];
        Session::put('campo.items', $items);

        Notification::make()
            ->title('Item adicionado! Preencha os dados de inspeção.')
            ->success()
            ->send();

        $this->redirect(FillItemData::getUrl());
    }

    public function getServiceOrderInfo(): ?ServiceOrder
    {
        $osId = $this->data['service_order_id'] ?? Session::get('campo.service_order_id');
        if (! $osId) {
            return null;
        }

        return ServiceOrder::with('client')->find($osId);
    }
}
