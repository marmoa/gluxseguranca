<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagConsumption extends Model
{
    protected $fillable = [
        'tag_inventory_id',
        'service_order_id',
        'quantity_used',
        'consumed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_used' => 'integer',
            'consumed_at'   => 'datetime',
        ];
    }

    public function tagInventory(): BelongsTo
    {
        return $this->belongsTo(TagInventory::class);
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }
}
