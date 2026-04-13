<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TraceabilitySetting;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Gera códigos de rastreabilidade sequenciais a partir de faixas configuráveis.
 *
 * Cada configuração define: digit_count (4 ou 6), range_start, range_end, last_used.
 * Thread-safe via lockForUpdate() dentro de uma transação.
 */
class TraceabilityCodeService
{
    /**
     * Gera o próximo código disponível para o digit_count informado.
     *
     * @throws RuntimeException quando a faixa está esgotada ou não existe configuração.
     */
    public function generate(int $digitCount): string
    {
        return DB::transaction(function () use ($digitCount) {
            /** @var TraceabilitySetting $setting */
            $setting = TraceabilitySetting::where('digit_count', $digitCount)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            $next = ($setting->last_used > 0)
                ? $setting->last_used + 1
                : $setting->range_start;

            if ($next > $setting->range_end) {
                throw new RuntimeException(
                    "Faixa de rastreabilidade esgotada para {$digitCount} dígitos. " .
                    "Configure uma nova faixa em Configurações → Rastreabilidade."
                );
            }

            $setting->update(['last_used' => $next]);

            return str_pad((string) $next, $digitCount, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Gera múltiplos códigos em sequência (para cadastro em lote).
     *
     * @return string[]
     * @throws RuntimeException quando a faixa não comporta a quantidade solicitada.
     */
    public function generateBatch(int $digitCount, int $quantity): array
    {
        return DB::transaction(function () use ($digitCount, $quantity) {
            /** @var TraceabilitySetting $setting */
            $setting = TraceabilitySetting::where('digit_count', $digitCount)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            $start = ($setting->last_used > 0)
                ? $setting->last_used + 1
                : $setting->range_start;

            $end = $start + $quantity - 1;

            if ($end > $setting->range_end) {
                $available = max(0, $setting->range_end - $start + 1);
                throw new RuntimeException(
                    "Faixa insuficiente: solicitados {$quantity} códigos, disponíveis {$available}."
                );
            }

            $setting->update(['last_used' => $end]);

            $codes = [];
            for ($n = $start; $n <= $end; $n++) {
                $codes[] = str_pad((string) $n, $digitCount, '0', STR_PAD_LEFT);
            }

            return $codes;
        });
    }

    /**
     * Verifica se um código está dentro da faixa configurada.
     */
    public function isValid(string $code): bool
    {
        $digitCount = strlen($code);

        $setting = TraceabilitySetting::where('digit_count', $digitCount)
            ->where('is_active', true)
            ->first();

        if (! $setting) {
            return false;
        }

        $numeric = (int) $code;
        return $numeric >= $setting->range_start && $numeric <= $setting->range_end;
    }
}
