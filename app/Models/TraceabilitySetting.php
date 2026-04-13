<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TraceabilitySetting extends Model
{
    protected $fillable = [
        'digit_count',
        'range_start',
        'range_end',
        'last_used',
        'label',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'digit_count' => 'integer',
            'range_start' => 'integer',
            'range_end'   => 'integer',
            'last_used'   => 'integer',
            'is_active'   => 'boolean',
        ];
    }

    public function remainingCodes(): int
    {
        return max(0, $this->range_end - ($this->last_used ?: $this->range_start - 1));
    }

    public function usagePercent(): float
    {
        $total = $this->range_end - $this->range_start + 1;
        $used  = max(0, ($this->last_used ?: $this->range_start - 1) - $this->range_start + 1);
        return $total > 0 ? round(($used / $total) * 100, 1) : 0;
    }

    public function scopeActive($query): mixed
    {
        return $query->where('is_active', true);
    }
}
