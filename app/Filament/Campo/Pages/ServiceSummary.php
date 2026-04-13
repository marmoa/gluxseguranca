<?php

declare(strict_types=1);

namespace App\Filament\Campo\Pages;

use App\Enums\ServiceOrderStatus;
use App\Models\ServiceOrder;
use App\Services\InspectionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

class ServiceSummary extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Operações de Campo';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Resumo do Serviço';

    protected static ?string $title = 'Resumo do Serviço';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.campo.pages.service-summary';

    public ?int $service_order_id = null;

    public array $summary = [];

    public function getTotalApproved(): int
    {
        return array_sum(array_column($this->summary, 'approved'));
    }

    public function getTotalRejected(): int
    {
        return array_sum(array_column($this->summary, 'rejected'));
    }

    public function getTotalPending(): int
    {
        return array_sum(array_column($this->summary, 'pending'));
    }

    public function mount(): void
    {
        $this->service_order_id = Session::get('campo.completed_os_id')
            ?? Session::get('campo.service_order_id');

        if ($this->service_order_id) {
            $serviceOrder = ServiceOrder::find($this->service_order_id);
            if ($serviceOrder) {
                /** @var InspectionService $service */
                $service       = app(InspectionService::class);
                $this->summary = $service->getOrderSummary($serviceOrder);
            }
        }
    }

    public function getServiceOrder(): ?ServiceOrder
    {
        if (! $this->service_order_id) {
            return null;
        }
        return ServiceOrder::with('client')->find($this->service_order_id);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_more_items')
                ->label('Adicionar Mais Itens')
                ->icon('heroicon-o-plus')
                ->color('gray')
                ->url(AddItems::getUrl()),

            Action::make('close_service_order')
                ->label('Fechar OS')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Fechar Ordem de Serviço?')
                ->modalDescription('Após fechar, não será possível adicionar mais itens. Deseja continuar?')
                ->modalSubmitActionLabel('Sim, fechar OS')
                ->action(function () {
                    $serviceOrder = $this->getServiceOrder();
                    if (! $serviceOrder) {
                        return;
                    }

                    $serviceOrder->update([
                        'status'       => ServiceOrderStatus::Completed,
                        'completed_at' => now(),
                    ]);

                    Session::forget(['campo.completed_os_id', 'campo.service_order_id', 'campo.items']);

                    Notification::make()
                        ->title("OS #{$serviceOrder->number} fechada com sucesso!")
                        ->success()
                        ->send();

                    $this->redirect(\App\Filament\Campo\Resources\ServiceOrderResource::getUrl('index'));
                }),
        ];
    }
}
