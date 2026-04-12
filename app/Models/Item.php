<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'standard_id',
        'tag_id',
        'name',
        'digit_count',
        'expiration_months',
        'photo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'digit_count'       => 'integer',
            'expiration_months' => 'integer',
            'is_active'         => 'boolean',
        ];
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'item_attribute')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'item_attribute_value', 'item_id', 'attribute_value_id')
            ->withPivot('attribute_id')
            ->withTimestamps();
    }

    public function norms(): BelongsToMany
    {
        return $this->belongsToMany(Norm::class, 'item_norm')->withTimestamps();
    }

    public function scopeActive($query): mixed
    {
        return $query->where('is_active', true);
    }
}
