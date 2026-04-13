<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagDistribution extends Model
{
    protected $fillable = [
        'tag_inventory_id',
        'distributed_to',
        'quantity',
        'distributed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'        => 'integer',
            'distributed_at'  => 'datetime',
        ];
    }

    public function tagInventory(): BelongsTo
    {
        return $this->belongsTo(TagInventory::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_to');
    }
}
