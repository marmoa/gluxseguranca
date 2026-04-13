<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionValue extends Model
{
    protected $fillable = [
        'inspection_id',
        'attribute_id',
        'text_value',
        'attribute_value_id',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }

    /**
     * Retorna o valor legível independente do tipo do atributo.
     */
    public function getDisplayValueAttribute(): string
    {
        if ($this->attribute_value_id && $this->attributeValue) {
            return $this->attributeValue->value;
        }
        return (string) ($this->text_value ?? '');
    }
}
