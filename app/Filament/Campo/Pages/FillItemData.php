<?php

declare(strict_types=1);

namespace App\Filament\Campo\Pages;

use App\Enums\InspectionResult;
use App\Enums\RejectionCategory;
use App\Models\AttributeValue;
use App\Models\Item;
use App\Models\ServiceOrder;
use App\Services\InspectionService;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

class FillItemData extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.campo.pages.fill-item-data';

    protected static ?string $title = 'Preencher Dados de Inspeção';

    /** Estado do formulário */
    public array $data = [];

    /** Item atual (carregado no mount) */
    public ?int $service_order_id = null;
    public ?int $item_id          = null;
    public int  $quantity          = 1;

    public function mount(): void
    {
        $items = Session::get('campo.items', []);

        if (empty($items)) {
            $this->redirect(AddItems::getUrl());
            return;
        }

        $pending = end($items);
        $this->service_order_id = (int) $pending['service_order_id'];
        $this->item_id          = (int) $pending['item_id'];
        $this->quantity         = (int) $pending['quantity'];

        $this->form->fill([
            'result'             => 'approved',
            'rejection_category' => null,
            'rejection_notes'    => null,
            'attribute_values'   => [],
        ]);
    }

    public function getCurrentItem(): ?Item
    {
        if (! $this->item_id) {
            return null;
        }

        return Item::with(['attributes.values'])->find($this->item_id);
    }

    public function getCurrentServiceOrder(): ?ServiceOrder
    {
        if (! $this->service_order_id) {
            return null;
        }

        return ServiceOrder::with('client')->find($this->service_order_id);
    }

    public function form(Form $form): Form
    {
        $item   = $this->getCurrentItem();
        $schema = [];

        // Campo de resultado (aprovado / reprovado)
        $schema[] = Radio::make('result')
            ->label('Resultado da Inspeção')
            ->options([
                'approved' => '✅ Aprovado',
                'rejected' => '❌ Reprovado',
            ])
            ->inline()
            ->required()
            ->live()
            ->default('approved');

        // Campos de reprovação — visíveis somente quando reprovado
        $schema[] = Select::make('rejection_category')
            ->label('Categoria da Reprovação')
            ->options(RejectionCategory::class)
            ->visible(fn (Get $get) => $get('result') === 'rejected')
            ->required(fn (Get $get) => $get('result') === 'rejected');

        $schema[] = Textarea::make('rejection_notes')
            ->label('Observações da Reprovação')
            ->rows(3)
            ->visible(fn (Get $get) => $get('result') === 'rejected');

        // Atributos dinâmicos do item
        if ($item) {
            foreach ($item->attributes as $attribute) {
                if ($attribute->isSelect()) {
                    $schema[] = Select::make("attribute_values.{$attribute->id}")
                        ->label($attribute->name)
                        ->options(
                            $attribute->values->pluck('value', 'id')->toArray()
                        )
                        ->searchable();
                } else {
                    $schema[] = TextInput::make("attribute_values.{$attribute->id}")
                        ->label($attribute->name);
                }
            }
        }

        return $form
            ->schema($schema)
            ->statePath('data');
    }

    public function saveInspection(): void
    {
        $data = $this->form->getState();

        $item         = $this->getCurrentItem();
        $serviceOrder = $this->getCurrentServiceOrder();

        if (! $item || ! $serviceOrder) {
            Notification::make()->title('Dados inválidos.')->danger()->send();
            return;
        }

        $result   = InspectionResult::from($data['result'] ?? 'approved');
        $category = isset($data['rejection_category']) && $data['rejection_category']
            ? RejectionCategory::from($data['rejection_category'])
            : null;

        /** @var InspectionService $service */
        $service = app(InspectionService::class);

        try {
            $service->createBatch(
                serviceOrder:      $serviceOrder,
                item:              $item,
                quantity:          $this->quantity,
                attributeValues:   $data['attribute_values'] ?? [],
                defaultResult:     $result,
                rejectionCategory: $category,
                rejectionNotes:    $data['rejection_notes'] ?? null,
            );
        } catch (\RuntimeException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
            return;
        }

        // Remove o item atual da fila de sessão
        $items = Session::get('campo.items', []);
        array_pop($items);
        Session::put('campo.items', $items);

        Notification::make()
            ->title("Inspeção registrada para {$this->quantity}x {$item->name}.")
            ->success()
            ->send();

        // Mais itens na fila → preencher o próximo
        if (! empty($items)) {
            $this->redirect(FillItemData::getUrl());
            return;
        }

        // Todos os itens concluídos → ir para o resumo
        Session::put('campo.completed_os_id', $this->service_order_id);
        Session::forget('campo.items');
        $this->redirect(ServiceSummary::getUrl());
    }

    public function cancelAndGoBack(): void
    {
        $items = Session::get('campo.items', []);
        array_pop($items);
        Session::put('campo.items', $items);

        $this->redirect(AddItems::getUrl());
    }
}
