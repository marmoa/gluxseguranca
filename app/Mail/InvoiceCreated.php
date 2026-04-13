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

class InvoiceCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Fatura {$this->invoice->number} — {$this->invoice->client->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoices.created',
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->invoice->hasPdf()) {
            $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromStorageDisk('public', $this->invoice->pdf_path)
                ->as("fatura-{$this->invoice->number}.pdf")
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
