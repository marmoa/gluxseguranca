<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    protected $fillable = ['name', 'abbreviation'];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
