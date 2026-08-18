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
                'belum_bayar' => $query->where(function ($q) {
                    // Invoice-synced: nothing paid, balance outstanding
                    $q->where(function ($iq) {
                        $iq->whereRaw(Registration::SQL_HAS_INVOICE)
                            ->whereRaw(Registration::SQL_NOT_CANCELLED)
                            ->whereRaw('COALESCE(total_paid, 0) = 0')
                            ->whereRaw(Registration::SQL_BALANCE.' > 0');
                    })
                        // Manual: legacy status pending
                        ->orWhere(function ($mq) {
                            $mq->whereRaw(Registration::SQL_NO_INVOICE)
                                ->where('payment_status', 'pending');
                        });
                }),
                'partial' => $query->where(function ($q) {
                    // Invoice-synced: something paid, balance outstanding
                    $q->where(function ($iq) {
                        $iq->whereRaw(Registration::SQL_HAS_INVOICE)
                            ->whereRaw(Registration::SQL_NOT_CANCELLED)
                            ->whereRaw('COALESCE(total_paid, 0) > 0')
                            ->whereRaw(Registration::SQL_BALANCE.' > 0');
                    })
                        // Manual: legacy status deposit
                        ->orWhere(function ($mq) {
                            $mq->whereRaw(Registration::SQL_NO_INVOICE)
                                ->where('payment_status', 'deposit');
                        });
                }),
                'paid' => $query->where(function ($q) {
                    // Invoice-synced: fully settled (or overpaid)
                    $q->where(function ($iq) {
                        $iq->whereRaw(Registration::SQL_HAS_INVOICE)
                            ->whereRaw(Registration::SQL_BALANCE.' <= 0');
                    })
                        // Manual: legacy status paid
                        ->orWhere(function ($mq) {
                            $mq->whereRaw(Registration::SQL_NO_INVOICE)
                                ->where('payment_status', 'paid');
                        });
                }),
                'cancelled' => $query->whereRaw("LOWER(TRIM(COALESCE(invoice_status, ''))) = 'cancelled'"),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $term = '%'.strtolower(trim((string) $request->string('search'))).'%';

            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw("LOWER(COALESCE(invoice_no, '')) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(COALESCE(pic_utama, '')) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(COALESCE(pic_in_house, '')) LIKE ?", [$term])
                    ->orWhereHas('departure.package', function ($p) use ($term) {
                        $p->whereRaw('LOWER(name) LIKE ?', [$term]);
                    });
            });
        }

        // KPI totals over the PIC-scoped set (not status/search filtered)
        // so the cards stay consistent while filtering the table.
        $baseQuery = Registration::query()->forPic($picFilter);

        $stats = (clone $baseQuery)->selectRaw('
                COUNT(*) AS total,
                '.$this->sumCase('cancelled').',
                '.$this->sumCase('belum_bayar').',
                '.$this->sumCase('partial').',
                '.$this->sumCase('paid').',
                '.$this->sumCase('outstanding').'
            ')
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

    /**
     * Build a SUM(CASE...) expression per status. Mirrors the model's
     * derived_payment_status logic exactly, including the manual-record
     * fallback to the legacy payment_status field.
     */
    private function sumCase(string $status): string
    {
        $hasInvoice = Registration::SQL_HAS_INVOICE;
        $noInvoice = Registration::SQL_NO_INVOICE;
        $notCancelled = Registration::SQL_NOT_CANCELLED;
        $balance = Registration::SQL_BALANCE;

        return match ($status) {
            'cancelled' => "SUM(CASE WHEN LOWER(TRIM(COALESCE(invoice_status, ''))) = 'cancelled' THEN 1 ELSE 0 END) AS cancelled",
            'belum_bayar' => "SUM(CASE WHEN ($hasInvoice AND $notCancelled AND COALESCE(total_paid, 0) = 0 AND $balance > 0)
                    OR ($noInvoice AND payment_status = 'pending')
                    THEN 1 ELSE 0 END) AS belum_bayar",
            'partial' => "SUM(CASE WHEN ($hasInvoice AND $notCancelled AND COALESCE(total_paid, 0) > 0 AND $balance > 0)
                    OR ($noInvoice AND payment_status = 'deposit')
                    THEN 1 ELSE 0 END) AS partial",
            'paid' => "SUM(CASE WHEN ($hasInvoice AND $balance <= 0)
                    OR ($noInvoice AND payment_status = 'paid')
                    THEN 1 ELSE 0 END) AS paid",
            'outstanding' => "SUM(CASE WHEN $notCancelled THEN $balance ELSE 0 END) AS outstanding",
        };
    }
}
