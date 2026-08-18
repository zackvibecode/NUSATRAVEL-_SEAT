@extends('layouts.app')

@section('title', 'Payment Alert')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">Payment Alert</h2>
            <p class="text-sm text-charcoal mt-2">
                @if ($picScope)
                    Showing payment records where you are PIC Utama or PIC In House.
                @else
                    Payment status across all customers and PICs.
                @endif
            </p>
        </div>
    </div>

    <!-- KPI cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4">
        <div class="bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight mb-3">Belum Bayar</p>
            <p class="text-2xl font-semibold leading-none tracking-tight text-brand">{{ $stats['belum_bayar'] }}</p>
            <p class="text-xs text-charcoal/70 mt-1.5 font-medium">no payment yet</p>
        </div>
        <div class="bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight mb-3">Partial</p>
            <p class="text-2xl font-semibold leading-none tracking-tight text-warning">{{ $stats['partial'] }}</p>
            <p class="text-xs text-charcoal/70 mt-1.5 font-medium">deposit paid, balance left</p>
        </div>
        <div class="bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight mb-3">Paid</p>
            <p class="text-2xl font-semibold leading-none tracking-tight text-positive">{{ $stats['paid'] }}</p>
            <p class="text-xs text-charcoal/70 mt-1.5 font-medium">fully settled</p>
        </div>
        <div class="bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight mb-3">Total Outstanding</p>
            <p class="text-2xl font-semibold leading-none tracking-tight">RM {{ number_format($stats['outstanding'], 2) }}</p>
            <p class="text-xs text-charcoal/70 mt-1.5 font-medium">{{ $activeAlerts }} record(s) need follow-up</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg border border-line p-4 sm:p-5 mb-4">
        <form method="GET" action="{{ route('payment-alerts.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[240px]">
                <label for="search" class="block text-[11px] font-semibold text-charcoal mb-1.5">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-charcoal" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $searchFilter }}"
                        placeholder="Search customer, invoice, PIC, package..."
                        class="w-full rounded-xl border border-line bg-white pl-9 pr-4 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                    >
                </div>
            </div>
            <div>
                <label for="status" class="block text-[11px] font-semibold text-charcoal mb-1.5">Status</label>
                <select name="status" id="status" class="rounded-xl border border-line bg-white px-3 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all min-w-[10rem]">
                    <option value="" @selected($statusFilter === '')>All Statuses</option>
                    <option value="belum_bayar" @selected($statusFilter === 'belum_bayar')>Belum Bayar</option>
                    <option value="partial" @selected($statusFilter === 'partial')>Partial</option>
                    <option value="paid" @selected($statusFilter === 'paid')>Paid</option>
                    <option value="cancelled" @selected($statusFilter === 'cancelled')>Cancelled</option>
                </select>
            </div>
            <button type="submit"
                    class="bg-brand hover:bg-brand-hover text-white text-xs font-bold rounded-full px-5 py-2.5 transition-all duration-150 hover:scale-[1.03] shadow-sm">
                Filter
            </button>
            <a href="{{ route('payment-alerts.index') }}" class="text-xs font-semibold text-charcoal hover:text-ink py-2.5">Reset</a>
        </form>
    </div>

    <!-- Payment table -->
    <div class="bg-white rounded-lg border border-line overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="text-left text-charcoal border-b border-line bg-fog/50">
                        <th class="px-5 py-4 font-semibold">Customer</th>
                        <th class="px-5 py-4 font-semibold">Invoice No.</th>
                        <th class="px-5 py-4 font-semibold">Package</th>
                        <th class="px-5 py-4 font-semibold">PIC Utama</th>
                        <th class="px-5 py-4 font-semibold">PIC In House</th>
                        <th class="px-5 py-4 font-semibold text-right">Invoice Amount</th>
                        <th class="px-5 py-4 font-semibold text-right">Total Paid</th>
                        <th class="px-5 py-4 font-semibold text-right">Balance</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold text-right">Invoice</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($payments as $payment)
                        <tr class="transition-colors hover:bg-fog/50 {{ $payment->requires_follow_up ? 'bg-brand-soft/30' : '' }}">
                            <td class="px-5 py-4">
                                <p class="font-bold">{{ $payment->name }}</p>
                                <p class="text-xs text-charcoal font-medium mt-0.5">{{ $payment->phone }}</p>
                            </td>
                            <td class="px-5 py-4 text-charcoal font-semibold">{{ $payment->invoice_no ?? '—' }}</td>
                            <td class="px-5 py-4 text-charcoal font-medium">
                                {{ $payment->departure?->package?->name ?? '—' }}
                                @if ($payment->departure?->departure_date)
                                    <span class="block text-xs text-charcoal/70">{{ $payment->departure->departure_date->format('d M Y') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-charcoal font-medium">{{ $payment->pic_utama ?? '—' }}</td>
                            <td class="px-5 py-4 text-charcoal font-medium">{{ $payment->pic_in_house ?? '—' }}</td>
                            <td class="px-5 py-4 text-right font-semibold">{{ $payment->invoice_amount !== null ? 'RM '.number_format((float) $payment->invoice_amount, 2) : '—' }}</td>
                            <td class="px-5 py-4 text-right font-semibold {{ $payment->total_paid > 0 ? 'text-positive' : '' }}">{{ $payment->total_paid !== null ? 'RM '.number_format((float) $payment->total_paid, 2) : '—' }}</td>
                            <td class="px-5 py-4 text-right font-bold {{ $payment->balance > 0 ? 'text-brand' : 'text-positive' }}">{{ $payment->invoice_amount !== null ? 'RM '.number_format($payment->balance, 2) : '—' }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold {{ $payment->payment_status_color }}">
                                    {{ $payment->payment_status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if ($payment->invoice_url)
                                    <a href="{{ $payment->invoice_url }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 text-brand hover:text-brand-hover text-sm font-bold">
                                        View
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                @else
                                    <span class="text-charcoal font-medium text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-charcoal font-medium">
                                @if ($picScope)
                                    No payment records assigned to you as PIC yet.
                                @else
                                    No payment records yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($payments->hasPages())
        <div class="bg-white rounded-lg border border-line px-6 py-4 mt-4">
            {{ $payments->links() }}
        </div>
    @endif
@endsection
