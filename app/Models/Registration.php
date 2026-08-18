<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use SoftDeletes;

    /**
     * Shared SQL fragments — single quotes only, so they work on both
     * SQLite (local) and PostgreSQL (production).
     */
    public const SQL_HAS_INVOICE = '(invoice_no IS NOT NULL OR invoice_amount IS NOT NULL OR total_paid IS NOT NULL OR invoice_status IS NOT NULL)';

    public const SQL_NO_INVOICE = '(invoice_no IS NULL AND invoice_amount IS NULL AND total_paid IS NULL AND invoice_status IS NULL)';

    public const SQL_NOT_CANCELLED = "LOWER(TRIM(COALESCE(invoice_status, ''))) != 'cancelled'";

    public const SQL_BALANCE = 'COALESCE(invoice_amount, 0) - COALESCE(total_paid, 0)';

    protected $fillable = [
        'departure_id',
        'name',
        'phone',
        'pax',
        'payment_status',
        'need_partner',
        'partner_gender',
        'notes',
        'invoice_no',
        'pic_utama',
        'pic_in_house',
        'invoice_status',
        'invoice_amount',
        'total_paid',
        'invoice_url',
    ];

    protected function casts(): array
    {
        return [
            'pax' => 'integer',
            'need_partner' => 'boolean',
            'invoice_amount' => 'decimal:2',
            'total_paid' => 'decimal:2',
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

    /**
     * Has this registration been synced from an invoice source?
     * Records without invoice data keep using the legacy manual
     * payment_status field (pending/deposit/paid).
     */
    public function getHasInvoiceAttribute(): bool
    {
        return filled($this->invoice_no)
            || filled($this->invoice_status)
            || $this->invoice_amount !== null
            || $this->total_paid !== null;
    }

    /**
     * Current balance / outstanding = invoice amount - total paid.
     */
    public function getBalanceAttribute(): float
    {
        $amount = (float) ($this->invoice_amount ?? 0);
        $paid = (float) ($this->total_paid ?? 0);

        return round($amount - $paid, 2);
    }

    /**
     * Payment status:
     * - cancelled   : invoice status is cancelled
     * - belum_bayar : total paid = 0 and balance > 0
     * - partial     : total paid > 0 and balance > 0
     * - paid        : balance <= 0
     * Manual records (no invoice data synced yet) fall back to the
     * legacy payment_status field (pending/deposit/paid).
     */
    public function getDerivedPaymentStatusAttribute(): string
    {
        if (strtolower(trim((string) $this->invoice_status)) === 'cancelled') {
            return 'cancelled';
        }

        if (! $this->has_invoice) {
            return match ($this->payment_status) {
                'paid' => 'paid',
                'deposit' => 'partial',
                default => 'belum_bayar',
            };
        }

        $paid = (float) ($this->total_paid ?? 0);
        $balance = $this->balance;

        if ($paid <= 0 && $balance > 0) {
            return 'belum_bayar';
        }

        if ($paid > 0 && $balance > 0) {
            return 'partial';
        }

        return 'paid';
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->derived_payment_status) {
            'paid' => 'bg-positive-soft text-positive',
            'partial' => 'bg-warning-soft text-warning',
            'belum_bayar' => 'bg-brand-soft text-brand',
            'cancelled' => 'bg-fog text-charcoal',
            default => 'bg-fog text-charcoal',
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->derived_payment_status) {
            'paid' => 'Paid',
            'partial' => 'Partial',
            'belum_bayar' => 'Belum Bayar',
            'cancelled' => 'Cancelled',
            default => '—',
        };
    }

    /**
     * Does this record still require payment follow-up?
     * (Belum Bayar + Partial — used for the alert badge count.)
     */
    public function getRequiresFollowUpAttribute(): bool
    {
        return in_array($this->derived_payment_status, ['belum_bayar', 'partial'], true);
    }

    /**
     * Scope: records that still require payment follow-up —
     * invoice-synced (Belum Bayar + Partial) and manual records
     * still pending/deposit. Used for the alert badge count.
     */
    public function scopeRequiresPaymentFollowUp(Builder $query): Builder
    {
        return $query->where(function (Builder $outer) {
            // Invoice-synced: not cancelled with outstanding balance
            $outer->where(function (Builder $q) {
                $q->whereRaw(self::SQL_HAS_INVOICE)
                    ->where(function (Builder $q2) {
                        $q2->whereNull('invoice_status')
                            ->orWhereRaw(self::SQL_NOT_CANCELLED);
                    })
                    ->whereRaw(self::SQL_BALANCE.' > 0');
            });

            // Manual (no invoice data): legacy status still pending/deposit
            $outer->orWhere(function (Builder $q) {
                $q->whereRaw(self::SQL_NO_INVOICE)
                    ->whereIn('payment_status', ['pending', 'deposit']);
            });
        });
    }

    /**
     * Scope registrations by PIC name (case-insensitive, matches either
     * PIC Utama or PIC In House). Null/empty = no filter (admin sees all).
     */
    public function scopeForPic(Builder $query, ?string $picName): Builder
    {
        $name = strtolower(trim((string) $picName));

        if ($name === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($name) {
            $q->whereRaw("LOWER(TRIM(COALESCE(pic_utama, ''))) = ?", [$name])
                ->orWhereRaw("LOWER(TRIM(COALESCE(pic_in_house, ''))) = ?", [$name]);
        });
    }
}
