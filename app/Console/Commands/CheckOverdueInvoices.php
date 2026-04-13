<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Mail\InvoiceOverdue;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckOverdueInvoices extends Command
{
    protected $signature   = 'invoices:check-overdue';
    protected $description = 'Marca faturas vencidas como overdue e envia notificações por e-mail';

    public function handle(): int
    {
        // 1. Busca faturas pendentes/enviadas com due_date passada
        $overdueInvoices = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Pending->value, InvoiceStatus::Sent->value])
            ->where('due_date', '<', now()->startOfDay())
            ->with(['client'])
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('Nenhuma fatura vencida encontrada.');
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($overdueInvoices as $invoice) {
            // 2. Atualiza status para overdue
            $invoice->update([
                'status'     => InvoiceStatus::Overdue,
                'overdue_at' => now(),
            ]);

            // 3. Envia e-mail de notificação para o cliente
            $client = $invoice->client;
            if ($client && filled($client->email)) {
                try {
                    Mail::to($client->email)->queue(new InvoiceOverdue($invoice));
                } catch (\Throwable $e) {
                    $this->warn("Falha ao enviar e-mail para {$client->email}: {$e->getMessage()}");
                }
            }

            $count++;
            $this->line("  ✔ Fatura {$invoice->number} marcada como vencida.");
        }

        $this->info("Total: {$count} fatura(s) processada(s).");

        return self::SUCCESS;
    }
}
