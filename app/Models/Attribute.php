<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AttributeInputType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'input_type', 'is_active'];

    protected function casts(): array
    {
        return [
            'input_type' => AttributeInputType::class,
            'is_active'  => 'boolean',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_attribute')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function standards(): BelongsToMany
    {
        return $this->belongsToMany(Standard::class, 'standard_attributes')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function scopeActive($query): mixed
    {
        return $query->where('is_active', true);
    }

    public function isSelect(): bool
    {
        return $this->input_type === AttributeInputType::Select;
    }
}
