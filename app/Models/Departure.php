<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departure extends Model
{
    protected $fillable = [
        'package_id',
        'departure_date',
        'return_date',
        'total_seats',
        'price',
        'airline',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'price' => 'decimal:2',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Total registered pax = SUM of all registration pax.
     * Never count rows — one row can represent many pax (PRD section 9).
     */
    public function getRegisteredPaxAttribute(): int
    {
        return (int) $this->registrations()->sum('pax');
    }

    /**
     * Available seats are always calculated, never stored (PRD section 8).
     */
    public function getAvailableSeatsAttribute(): int
    {
        return $this->total_seats - $this->registered_pax;
    }

    public function getOccupancyPercentAttribute(): float
    {
        if ($this->total_seats <= 0) {
            return 0;
        }

        return round(($this->registered_pax / $this->total_seats) * 100, 1);
    }

    /**
     * Status rules (PRD section 10):
     * 1. Manually cancelled wins over everything.
     * 2. Departure date passed -> Departed.
     * 3. Available = 0 -> Full.
     * 4. Available 1-5 -> Almost Full.
     * 5. Available > 5 -> Open.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        if ($this->departure_date->isPast()) {
            return 'departed';
        }

        return match (true) {
            $this->available_seats <= 0 => 'full',
            $this->available_seats <= 5 => 'almost_full',
            default => 'open',
        };
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function scopeNotCancelled($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    public function scopeUpcoming($query)
    {
        return $query->notCancelled()
            ->where('departure_date', '>=', now()->toDateString());
    }
}
