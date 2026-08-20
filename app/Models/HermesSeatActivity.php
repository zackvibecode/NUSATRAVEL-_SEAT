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
        'activity_type',
        'actor_name',
        'activity_note',
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

    public function getActivityTypeLabelAttribute(): string
    {
        return match ($this->activity_type) {
            'registration_created' => 'Pendaftaran',
            'registration_updated' => 'Pax diubah',
            'registration_deleted' => 'Pembatalan',
            'departure_created' => 'Trip baru',
            'departure_updated' => 'Kapasiti diubah',
            'import' => 'Import Excel',
            default => $this->activity_type
                ? ucfirst(str_replace('_', ' ', $this->activity_type))
                : '',
        };
    }

    public function getUpdatedAtLabelAttribute(): string
    {
        return $this->created_at
            ->timezone('Asia/Kuala_Lumpur')
            ->format('j M Y, g:ia');
    }
}
