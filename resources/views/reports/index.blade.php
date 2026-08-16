@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-none">Monthly Report</h2>
            <p class="text-sm text-charcoal mt-2">Monthly totals for trips, capacity, registered pax and available seats (PRD 12).</p>
        </div>
        <a href="{{ route('reports.export', array_filter(request()->only(['month', 'year', 'package_id', 'destination', 'status', 'search']))) }}"
           class="inline-flex items-center gap-2 bg-white shadow-sm border border-line hover:shadow-md text-ink text-sm font-bold rounded-full px-6 py-3 transition-all duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export CSV
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-line p-4 sm:p-5 mb-4">
        @include('partials.trip-filters', ['filter' => $filter, 'showPackage' => false, 'showSort' => false])
    </div>

    <!-- Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-line p-4 sm:p-6 mb-4">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-brand-soft flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="font-bold text-base sm:text-lg tracking-tight">{{ $periodLabel }} Summary</h3>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <p class="text-[10px] font-semibold text-charcoal uppercase tracking-wider">Total Departures</p>
                <p class="text-xl sm:text-2xl font-black mt-1.5 leading-none">{{ $totalDepartures }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-charcoal uppercase tracking-wider">Total Capacity</p>
                <p class="text-xl sm:text-2xl font-black mt-1.5 leading-none">{{ $totalCapacity }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-charcoal uppercase tracking-wider">Registered Pax</p>
                <p class="text-xl sm:text-2xl font-black mt-1.5 leading-none">{{ $registeredPax }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-charcoal uppercase tracking-wider">Available Seats</p>
                <p class="text-xl sm:text-2xl font-black mt-1.5 leading-none {{ $availableSeats <= 0 ? 'text-brand' : 'text-positive' }}">{{ $availableSeats }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-charcoal uppercase tracking-wider">Overall Occupancy</p>
                <p class="text-xl sm:text-2xl font-black mt-1.5 leading-none">{{ $overallOccupancy }}%</p>
            </div>
        </div>
    </div>

    <!-- Departure breakdown � card rows -->
    <div class="space-y-3">
        @forelse ($departures as $departure)
            <div class="bg-white rounded-xl border border-line hover:shadow-md hover:border-brand/30 transition-all duration-150">
                <div class="p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="min-w-0">
                            <a href="{{ route('departures.show', $departure) }}"
                               class="font-black text-base tracking-tight hover:text-brand transition-colors">
                                {{ $departure->package->name }}
                            </a>
                            <p class="text-xs text-charcoal font-medium mt-0.5">{{ $departure->departure_date->format('d M Y') }}</p>
                        </div>
                        @include('partials.status-badge', ['status' => $departure->status_label])
                    </div>
                    <div class="grid grid-cols-3 gap-3 items-center">
                        <div>
                            <p class="text-[10px] font-semibold text-charcoal uppercase tracking-wider mb-0.5">Registered</p>
                            <p class="text-sm font-black text-ink">{{ $departure->registered_pax }}<span class="text-xs text-charcoal font-medium"> / {{ $departure->total_seats }}</span></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-charcoal uppercase tracking-wider mb-0.5">Available</p>
                            <p class="text-sm font-black {{ $departure->available_seats <= 0 ? 'text-brand' : 'text-positive' }}">{{ $departure->available_seats }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-charcoal uppercase tracking-wider mb-0.5">Occupancy</p>
                            <p class="text-sm font-black text-ink">{{ $departure->occupancy_percent }}%</p>
                        </div>
                    </div>
                    @php
                        $percent = $departure->total_seats > 0 ? round(($departure->registered_pax / $departure->total_seats) * 100) : 0;
                        $barColor = match ($departure->status_label) {
                            'full', 'departed', 'cancelled' => 'bg-brand',
                            'almost_full' => 'bg-warning',
                            default => 'bg-positive',
                        };
                    @endphp
                    <div class="mt-3 h-2 bg-fog rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $barColor }} transition-all duration-500" style="width: {{ min(100, $percent) }}%"></div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-line px-6 py-16 text-center">
                <div class="w-12 h-12 rounded-xl bg-fog flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-charcoal" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-charcoal font-medium text-sm">No departures found for {{ $periodLabel }}.</p>
            </div>
        @endforelse
    </div>

    @if ($departures->hasPages())
        <div class="bg-white rounded-xl shadow-sm border border-line px-6 py-4 mt-4">
            {{ $departures->links() }}
        </div>
    @endif
@endsection