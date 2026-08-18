<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentAlertController extends Controller
{
    /**
     * Payment alert list. Admin sees every PIC; sales users are filtered
     * server-side to records where they are PIC Utama or PIC In House.
     */
    public function index(Request $request): View
    {
        $picFilter = $request->user()->picFilterName();

        $query = Registration::query()
            ->with('departure.package')
            ->forPic($picFilter);

        if ($request->filled('status')) {
            match ($request->string('status')->toString()) {
                'belum_bayar' => $query->whereRaw('COALESCE(total_paid, 0) = 0')
                    ->whereRaw('COALESCE(invoice_amount, 0) - COALESCE(total_paid, 0) > 0')
                    ->where(fn ($q) => $q->whereNull('invoice_status')
                        ->orWhereRaw("LOWER(TRIM(invoice_status)) != 'cancelled'")),
                'partial' => $query->whereRaw('COALESCE(total_paid, 0) > 0')
                    ->whereRaw('COALESCE(invoice_amount, 0) - COALESCE(total_paid, 0) > 0')
                    ->where(fn ($q) => $q->whereNull('invoice_status')
                        ->orWhereRaw("LOWER(TRIM(invoice_status)) != 'cancelled'")),
                'paid' => $query->whereRaw('COALESCE(invoice_amount, 0) - COALESCE(total_paid, 0) <= 0'),
                'cancelled' => $query->whereRaw("LOWER(TRIM(invoice_status)) = 'cancelled'"),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $term = '%'.strtolower(trim((string) $request->string('search'))).'%';

            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(invoice_no, "")) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(pic_utama, "")) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(pic_in_house, "")) LIKE ?', [$term])
                    ->orWhereHas('departure.package', function ($p) use ($term) {
                        $p->whereRaw('LOWER(name) LIKE ?', [$term]);
                    });
            });
        }

        // Status filter changes the population, so totals must be computed
        // over the unfiltered PIC-scoped set for consistent KPI cards.
        $baseQuery = Registration::query()->forPic($picFilter);

        $stats = (clone $baseQuery)->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(invoice_status, ''))) = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
                SUM(CASE WHEN (invoice_no IS NOT NULL OR invoice_amount IS NOT NULL OR total_paid IS NOT NULL OR invoice_status IS NOT NULL)
                          AND (invoice_status IS NULL OR LOWER(TRIM(invoice_status)) != 'cancelled')
                          AND COALESCE(total_paid, 0) = 0
                          AND COALESCE(invoice_amount, 0) - COALESCE(total_paid, 0) > 0 THEN 1 ELSE 0 END) AS belum_bayar,
                SUM(CASE WHEN (invoice_no IS NOT NULL OR invoice_amount IS NOT NULL OR total_paid IS NOT NULL OR invoice_status IS NOT NULL)
                          AND (invoice_status IS NULL OR LOWER(TRIM(invoice_status)) != 'cancelled')
                          AND COALESCE(total_paid, 0) > 0
                          AND COALESCE(invoice_amount, 0) - COALESCE(total_paid, 0) > 0 THEN 1 ELSE 0 END) AS partial,
                SUM(CASE WHEN (invoice_no IS NOT NULL OR invoice_amount IS NOT NULL OR total_paid IS NOT NULL OR invoice_status IS NOT NULL)
                          AND (invoice_status IS NULL OR LOWER(TRIM(invoice_status)) != 'cancelled')
                          AND COALESCE(invoice_amount, 0) - COALESCE(total_paid, 0) <= 0 THEN 1 ELSE 0 END) AS paid,
                SUM(CASE WHEN (invoice_status IS NULL OR LOWER(TRIM(invoice_status)) != 'cancelled')
                          THEN COALESCE(invoice_amount, 0) - COALESCE(total_paid, 0) ELSE 0 END) AS outstanding
            ")
            ->first();

        $payments = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('payment-alerts.index', [
            'payments' => $payments,
            'stats' => [
                'total' => (int) ($stats->total ?? 0),
                'belum_bayar' => (int) ($stats->belum_bayar ?? 0),
                'partial' => (int) ($stats->partial ?? 0),
                'paid' => (int) ($stats->paid ?? 0),
                'cancelled' => (int) ($stats->cancelled ?? 0),
                'outstanding' => (float) ($stats->outstanding ?? 0),
            ],
            'statusFilter' => $request->string('status')->toString(),
            'searchFilter' => $request->string('search')->toString(),
            'picScope' => $picFilter,
            'activeAlerts' => (clone $baseQuery)->requiresPaymentFollowUp()->count(),
        ]);
    }
}
