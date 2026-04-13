<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceOverdue extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚠️ Fatura Vencida: {$this->invoice->number} — {$this->invoice->client->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoices.overdue',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
