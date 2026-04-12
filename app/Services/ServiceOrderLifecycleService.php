<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ServiceOrderStatus;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ServiceOrderLifecycleService
{
    /**
     * Inicia a execução da OS: Open → InProgress.
     */
    public function start(ServiceOrder $order): void
    {
        $this->transition($order, ServiceOrderStatus::InProgress, function () use ($order): void {
            $order->started_at = now();
        });
    }

    /**
     * Marca a OS como concluída: InProgress → Completed.
     */
    public function complete(ServiceOrder $order): void
    {
        $this->transition($order, ServiceOrderStatus::Completed, function () use ($order): void {
            $order->completed_at = now();
        });
    }

    /**
     * Fatura a OS: Completed → Billed.
     */
    public function bill(ServiceOrder $order): void
    {
        $this->transition($order, ServiceOrderStatus::Billed, function () use ($order): void {
            $order->billed_at = now();
        });
    }

    /**
     * Cancela a OS (de Open ou InProgress).
     */
    public function cancel(ServiceOrder $order): void
    {
        $this->transition($order, ServiceOrderStatus::Cancelled);
    }

    /**
     * Reabre a OS: Completed → InProgress.
     */
    public function reopen(ServiceOrder $order): void
    {
        $this->transition($order, ServiceOrderStatus::InProgress, function () use ($order): void {
            $order->completed_at = null;
        });
    }

    /**
     * Executa a transição de status com callback opcional e persiste em transação.
     *
     * @param  callable|null  $beforeSave  Callback para alterar campos antes de salvar.
     */
    private function transition(ServiceOrder $order, ServiceOrderStatus $next, ?callable $beforeSave = null): void
    {
        if (! $order->status->canTransitionTo($next)) {
            throw new RuntimeException(
                "Transição inválida: {$order->status->label()} → {$next->label()}"
            );
        }

        DB::transaction(function () use ($order, $next, $beforeSave): void {
            $order->status = $next;

            if ($beforeSave !== null) {
                $beforeSave();
            }

            $order->save();
        });
    }
}
