<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'name',
        'destination',
        'description',
        'status',
    ];

    public function departures(): HasMany
    {
        return $this->hasMany(Departure::class);
    }

    public function activeDepartures(): HasMany
    {
        return $this->departures()->where('status', '!=', 'cancelled');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
