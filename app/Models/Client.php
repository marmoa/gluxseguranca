<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_name',
        'trade_name',
        'cnpj',
        'phone',
        'email',
        'standard_id',
        'state_id',
        'city_id',
        'address',
        'neighborhood',
        'zip_code',
        'contact_name',
        'contact_phone',
        'contact_email',
        'contact_mobile',
        'segment',
        'cost_center',
        'base',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(ClientContract::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->trade_name ?? $this->company_name;
    }

    /**
     * Alias para exibição simples do nome do cliente (trade_name ?? company_name).
     */
    public function getNameAttribute(): string
    {
        return $this->trade_name ?? $this->company_name;
    }

    public function scopeActive($query): mixed
    {
        return $query->where('is_active', true);
    }
}
