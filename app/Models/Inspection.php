<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InspectionResult;
use App\Enums\RejectionCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inspection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_order_id',
        'service_order_item_id',
        'item_id',
        'traceability_code',
        'digit_count',
        'result',
        'rejection_category',
        'rejection_notes',
        'expires_at',
        'batch_sequence',
    ];

    protected function casts(): array
    {
        return [
            'result'             => InspectionResult::class,
            'rejection_category' => RejectionCategory::class,
            'expires_at'         => 'date',
            'digit_count'        => 'integer',
            'batch_sequence'     => 'integer',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function serviceOrderItem(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderItem::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(InspectionValue::class);
    }

    public function isApproved(): bool
    {
        return $this->result === InspectionResult::Approved;
    }

    public function isRejected(): bool
    {
        return $this->result === InspectionResult::Rejected;
    }

    public function scopePending($query): mixed
    {
        return $query->where('result', InspectionResult::Pending->value);
    }

    public function scopeApproved($query): mixed
    {
        return $query->where('result', InspectionResult::Approved->value);
    }

    public function scopeRejected($query): mixed
    {
        return $query->where('result', InspectionResult::Rejected->value);
    }

    public function scopeExpiringSoon($query, int $days = 30): mixed
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>=', now());
    }
}
