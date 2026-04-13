<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InspectionResult;
use App\Enums\RejectionCategory;
use App\Models\Inspection;
use App\Models\InspectionValue;
use App\Models\Item;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InspectionService
{
    public function __construct(
        private readonly TraceabilityCodeService $traceabilityService
    ) {}

    /**
     * Adiciona um item à OS e cria N registros de inspeção (um por unidade).
     * Os valores dos atributos são replicados de um único formulário preenchido pelo operador.
     *
     * @param  array<string, mixed>  $attributeValues  ['attribute_id' => 'value_or_id', ...]
     * @param  array<string, mixed>  $rejectionData    ['result', 'category', 'notes'] (opcional)
     * @return ServiceOrderItem
     */
    public function createBatch(
        ServiceOrder $serviceOrder,
        Item $item,
        int $quantity,
        array $attributeValues = [],
        ?InspectionResult $defaultResult = null,
        ?RejectionCategory $rejectionCategory = null,
        ?string $rejectionNotes = null,
    ): ServiceOrderItem {
        return DB::transaction(function () use (
            $serviceOrder, $item, $quantity, $attributeValues,
            $defaultResult, $rejectionCategory, $rejectionNotes
        ) {
            // 1. Cria ou reutiliza o ServiceOrderItem
            $orderItem = ServiceOrderItem::firstOrCreate(
                ['service_order_id' => $serviceOrder->id, 'item_id' => $item->id],
                ['quantity' => 0]
            );
            $orderItem->increment('quantity', $quantity);

            // 2. Gera os códigos de rastreabilidade em lote
            $codes = $this->traceabilityService->generateBatch($item->digit_count, $quantity);

            // 3. Calcula data de expiração
            $expiresAt = Carbon::now()->addMonths($item->expiration_months)->toDateString();

            // 4. Bulk-insere as inspeções
            $result = $defaultResult ?? InspectionResult::Pending;
            $now    = now();
            $inspectionRows = [];

            foreach ($codes as $seq => $code) {
                $inspectionRows[] = [
                    'service_order_id'      => $serviceOrder->id,
                    'service_order_item_id' => $orderItem->id,
                    'item_id'               => $item->id,
                    'traceability_code'     => $code,
                    'digit_count'           => $item->digit_count,
                    'result'                => $result->value,
                    'rejection_category'    => $rejectionCategory?->value,
                    'rejection_notes'       => $result === InspectionResult::Rejected ? $rejectionNotes : null,
                    'expires_at'            => $result === InspectionResult::Approved ? $expiresAt : null,
                    'batch_sequence'        => $seq + 1,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];
            }

            Inspection::insert($inspectionRows);

            // 5. Busca as inspeções recém-criadas para inserir os valores
            if (! empty($attributeValues)) {
                $inspections = Inspection::where('service_order_item_id', $orderItem->id)
                    ->whereIn('traceability_code', $codes)
                    ->pluck('id');

                $valueRows = [];
                foreach ($inspections as $inspectionId) {
                    foreach ($attributeValues as $attributeId => $value) {
                        $attribute = \App\Models\Attribute::find($attributeId);
                        if (! $attribute) continue;

                        $row = [
                            'inspection_id' => $inspectionId,
                            'attribute_id'  => $attributeId,
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ];

                        if ($attribute->isSelect()) {
                            $row['attribute_value_id'] = $value;
                            $row['text_value']         = null;
                        } else {
                            $row['text_value']         = $value;
                            $row['attribute_value_id'] = null;
                        }

                        $valueRows[] = $row;
                    }
                }

                if (! empty($valueRows)) {
                    InspectionValue::insert($valueRows);
                }
            }

            return $orderItem->refresh();
        });
    }

    /**
     * Aprova uma inspeção individual e atribui o código de rastreabilidade.
     */
    public function approve(Inspection $inspection): void
    {
        DB::transaction(function () use ($inspection) {
            $expiresAt = Carbon::now()
                ->addMonths($inspection->item->expiration_months)
                ->toDateString();

            $inspection->update([
                'result'             => InspectionResult::Approved->value,
                'rejection_category' => null,
                'rejection_notes'    => null,
                'expires_at'         => $expiresAt,
            ]);
        });
    }

    /**
     * Reprova uma inspeção individual.
     */
    public function reject(
        Inspection $inspection,
        RejectionCategory $category,
        ?string $notes = null
    ): void {
        DB::transaction(function () use ($inspection, $category, $notes) {
            $inspection->update([
                'result'             => InspectionResult::Rejected->value,
                'rejection_category' => $category->value,
                'rejection_notes'    => $notes,
                'expires_at'         => null,
                'traceability_code'  => null, // reprovado não recebe código
            ]);
        });
    }

    /**
     * Edita os valores de atributos de uma inspeção existente.
     *
     * @param  array<int, mixed>  $attributeValues  [attribute_id => valor]
     */
    public function updateValues(Inspection $inspection, array $attributeValues): void
    {
        DB::transaction(function () use ($inspection, $attributeValues) {
            foreach ($attributeValues as $attributeId => $value) {
                $attribute = \App\Models\Attribute::find($attributeId);
                if (! $attribute) continue;

                $data = $attribute->isSelect()
                    ? ['attribute_value_id' => $value, 'text_value' => null]
                    : ['text_value' => $value, 'attribute_value_id' => null];

                InspectionValue::updateOrCreate(
                    ['inspection_id' => $inspection->id, 'attribute_id' => $attributeId],
                    $data
                );
            }
        });
    }

    /**
     * Retorna o resumo de uma OS (totais por item, aprovados, reprovados, pendentes).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getOrderSummary(ServiceOrder $serviceOrder): array
    {
        return $serviceOrder->orderItems()
            ->with(['item', 'inspections'])
            ->get()
            ->map(function (ServiceOrderItem $orderItem) {
                $inspections = $orderItem->inspections;
                return [
                    'item'      => $orderItem->item,
                    'quantity'  => $orderItem->quantity,
                    'approved'  => $inspections->where('result', InspectionResult::Approved->value)->count(),
                    'rejected'  => $inspections->where('result', InspectionResult::Rejected->value)->count(),
                    'pending'   => $inspections->where('result', InspectionResult::Pending->value)->count(),
                ];
            })
            ->toArray();
    }
}
