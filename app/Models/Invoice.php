<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'number',
        'client_id',
        'service_order_id',
        'status',
        'amount',
        'tax_amount',
        'total_amount',
        'due_date',
        'pdf_path',
        'notes',
        'sent_at',
        'paid_at',
        'overdue_at',
    ];

    protected function casts(): array
    {
        return [
            'status'      => InvoiceStatus::class,
            'amount'      => 'decimal:2',
            'tax_amount'  => 'decimal:2',
            'total_amount' => 'decimal:2',
            'due_date'    => 'date',
            'sent_at'     => 'datetime',
            'paid_at'     => 'datetime',
            'overdue_at'  => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['number', 'status', 'amount', 'total_amount', 'due_date', 'pdf_path'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Escopos ────────────────────────────────────────────────
    public function scopePending($query)
    {
        return $query->where('status', InvoiceStatus::Pending);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', InvoiceStatus::Overdue);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->whereIn('status', [InvoiceStatus::Pending->value, InvoiceStatus::Sent->value])
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    // ── Auxiliares ────────────────────────────────────────────
    public function isOverdue(): bool
    {
        return $this->status === InvoiceStatus::Overdue
            || (
                in_array($this->status, [InvoiceStatus::Pending, InvoiceStatus::Sent])
                && $this->due_date < now()->startOfDay()
            );
    }

    public function hasPdf(): bool
    {
        return filled($this->pdf_path);
    }

    // ── Relacionamentos ───────────────────────────────────────
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }
}
