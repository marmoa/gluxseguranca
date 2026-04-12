<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'number',
        'client_id',
        'price_table_id',
        'user_id',
        'status',
        'valid_until',
        'total',
        'notes',
        'rejection_reason',
        'sent_at',
        'approved_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'status'      => QuoteStatus::class,
            'valid_until' => 'date',
            'total'       => 'decimal:2',
            'sent_at'     => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function priceTable(): BelongsTo
    {
        return $this->belongsTo(PriceTable::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function recalculateTotal(): void
    {
        $this->total = $this->items()->sum('subtotal');
        $this->save();
    }

    public function scopeForClient($query, int $clientId): mixed
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeActive($query): mixed
    {
        return $query->whereIn('status', [QuoteStatus::Draft, QuoteStatus::Sent]);
    }

    public function isEditable(): bool
    {
        return $this->status === QuoteStatus::Draft;
    }
}
