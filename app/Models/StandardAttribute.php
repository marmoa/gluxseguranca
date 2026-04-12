<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StandardAttribute extends Model
{
    protected $fillable = ['standard_id', 'attribute_id', 'sort_order'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function defaultValues(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'standard_attribute_values',
            'standard_attribute_id',
            'attribute_value_id'
        )->withTimestamps();
    }
}
