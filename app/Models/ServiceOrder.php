<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ServiceOrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'number',
        'client_id',
        'client_contract_id',
        'quote_id',
        'user_id',
        'status',
        'state_id',
        'city_id',
        'address',
        'temperature',
        'humidity',
        'notes',
        'scheduled_at',
        'started_at',
        'completed_at',
        'billed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ServiceOrderStatus::class,
            'scheduled_at' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'billed_at' => 'datetime',
            'temperature' => 'decimal:1',
            'humidity' => 'decimal:1',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ClientContract::class, 'client_contract_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', ServiceOrderStatus::Open);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', ServiceOrderStatus::InProgress);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', ServiceOrderStatus::Completed);
    }

    public function isEditable(): bool
    {
        return $this->status === ServiceOrderStatus::Open;
    }
}
