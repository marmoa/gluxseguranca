<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrderItem extends Model
{
    protected $fillable = [
        'service_order_id',
        'item_id',
        'quantity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function approvedCount(): int
    {
        return $this->inspections()->where('result', \App\Enums\InspectionResult::Approved->value)->count();
    }

    public function rejectedCount(): int
    {
        return $this->inspections()->where('result', \App\Enums\InspectionResult::Rejected->value)->count();
    }
}
