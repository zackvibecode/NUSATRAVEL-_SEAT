<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $fillable = [
        'departure_id',
        'name',
        'phone',
        'pax',
        'need_partner',
        'partner_gender',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pax' => 'integer',
            'need_partner' => 'boolean',
        ];
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }

    public function getPartnerLabelAttribute(): ?string
    {
        if (! $this->need_partner) {
            return null;
        }

        return $this->partner_gender === 'male' ? 'Male' : 'Female';
    }
}
