<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HermesSeatActivity extends Model
{
    protected $fillable = [
        'departure_id',
        'package_name',
        'departure_date',
        'seat_delta',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'seat_delta' => 'integer',
        ];
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }

    public function getSeatChangeLabelAttribute(): string
    {
        $prefix = $this->seat_delta > 0 ? '+' : '';

        return 'Seat '.$prefix.$this->seat_delta;
    }
}
