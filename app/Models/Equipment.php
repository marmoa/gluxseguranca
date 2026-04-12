<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'brand',
        'model',
        'serial_number',
        'certificate_number',
        'calibrated_at',
        'calibration_due_at',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'calibrated_at'      => 'date',
            'calibration_due_at' => 'date',
            'is_active'          => 'boolean',
        ];
    }

    public function isCalibrationOverdue(): bool
    {
        return $this->calibration_due_at?->isPast() ?? false;
    }

    public function scopeActive($query): mixed
    {
        return $query->where('is_active', true);
    }

    public function scopeOverdue($query): mixed
    {
        return $query->whereNotNull('calibration_due_at')
            ->where('calibration_due_at', '<', now());
    }
}
