@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight leading-none">Monthly Report</h2>
            <p class="text-sm text-charcoal mt-2">Monthly totals for trips, capacity, registered pax and available seats (PRD 12).</p>
        </div>
    </div>

    @include('partials.trip-filters', ['filter' => $filter, 'showPackage' => false])

    <!-- Summary -->
    <div class="bg-white rounded-3xl shadow-sm border border-line p-8 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="font-bold text-lg tracking-tight">{{ $periodLabel }} Summary</h3>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-6">
            <div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Total Departures</p>
                <p class="text-3xl font-black mt-2 leading-none">{{ $totalDepartures }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Total Capacity</p>
                <p class="text-3xl font-black mt-2 leading-none">{{ $totalCapacity }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Registered Pax</p>
                <p class="text-3xl font-black mt-2 leading-none">{{ $registeredPax }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Available Seats</p>
                <p class="text-3xl font-black mt-2 leading-none {{ $availableSeats <= 0 ? 'text-brand' : 'text-positive' }}">{{ $availableSeats }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Overall Occupancy</p>
                <p class="text-3xl font-black mt-2 leading-none">{{ $overallOccupancy }}%</p>
            </div>
        </div>
    </div>

    <!-- Per-trip breakdown -->
    <div class="bg-white rounded-3xl shadow-sm border border-line overflow-hidden">
        <div class="px-6 py-5 border-b border-line flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="font-bold text-lg tracking-tight">Departure Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-charcoal border-b border-line bg-fog/50">
                        <th class="px-6 py-4 font-semibold">
                            @include('partials.sort-link', ['filter' => $filter, 'column' => 'package_name', 'label' => 'Trip'])
                        </th>
                        <th class="px-6 py-4 font-semibold">
                            @include('partials.sort-link', ['filter' => $filter, 'column' => 'departure_date', 'label' => 'Date'])
                        </th>
                        <th class="px-6 py-4 font-semibold text-right">Registered</th>
                        <th class="px-6 py-4 font-semibold text-right">Capacity</th>
                        <th class="px-6 py-4 font-semibold text-right">Available</th>
                        <th class="px-6 py-4 font-semibold text-right">Occupancy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($departures as $departure)
                        <tr class="transition-colors hover:bg-fog/50">
                            <td class="px-6 py-4">
                                <a href="{{ route('departures.show', $departure) }}"
                                   class="font-bold text-ink hover:text-brand">
                                    {{ $departure->package->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-charcoal font-medium">{{ $departure->departure_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-right font-semibold">{{ $departure->registered_pax }}</td>
                            <td class="px-6 py-4 text-right text-charcoal">{{ $departure->total_seats }}</td>
                            <td class="px-6 py-4 text-right font-bold {{ $departure->available_seats <= 0 ? 'text-brand' : 'text-positive' }}">{{ $departure->available_seats }}</td>
                            <td class="px-6 py-4 text-right font-semibold">{{ $departure->occupancy_percent }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-charcoal font-medium">
                                No departures found for {{ $periodLabel }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
