<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'departure_id',
        'name',
        'phone',
        'pax',
        'payment_status',
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

    public function getPaymentLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Paid',
            'deposit' => 'Deposit',
            default => 'Pending',
        };
    }

    public function getPaymentColorAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-positive-soft text-positive',
            'deposit' => 'bg-warning-soft text-warning',
            default => 'bg-brand-soft text-brand',
        };
    }
}
