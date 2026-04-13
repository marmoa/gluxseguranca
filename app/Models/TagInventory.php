<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagInventory extends Model
{
    protected $table = 'tag_inventory';

    protected $fillable = [
        'tag_id',
        'batch_code',
        'initial_quantity',
        'current_quantity',
        'unit_cost',
        'minimum_stock',
        'received_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'initial_quantity' => 'integer',
            'current_quantity' => 'integer',
            'minimum_stock'    => 'integer',
            'unit_cost'        => 'decimal:2',
            'received_at'      => 'date',
        ];
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(TagDistribution::class);
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(TagConsumption::class);
    }

    public function totalDistributed(): int
    {
        return $this->distributions()->sum('quantity');
    }

    public function totalConsumed(): int
    {
        return $this->consumptions()->sum('quantity_used');
    }

    public function isBelowMinimum(): bool
    {
        return $this->current_quantity <= $this->minimum_stock;
    }

    public function scopeLowStock($query): mixed
    {
        return $query->whereColumn('current_quantity', '<=', 'minimum_stock');
    }
}
