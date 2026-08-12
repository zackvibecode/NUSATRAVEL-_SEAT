@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Page header -->
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight leading-none">Dashboard</h2>
            <p class="text-sm text-charcoal mt-2">How many pax are registered and how many seats remain?</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-charcoal font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ now()->format('l, d F Y') }}
        </div>
    </div>

    @include('partials.trip-filters', ['filter' => $filter])

    <!-- Summary cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Upcoming Trips</p>
            </div>
            <p class="text-4xl font-black leading-none">{{ $upcomingTrips }}</p>
        </div>
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Total Capacity</p>
            </div>
            <p class="text-4xl font-black leading-none">{{ $totalCapacity }}</p>
        </div>
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Registered Pax</p>
            </div>
            <p class="text-4xl font-black leading-none">{{ $registeredPax }}</p>
        </div>
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl {{ $availableSeats <= 0 ? 'bg-brand' : 'bg-positive-soft' }} flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $availableSeats <= 0 ? 'text-white' : 'text-positive' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Available Seats</p>
            </div>
            <p class="text-4xl font-black leading-none {{ $availableSeats <= 0 ? 'text-brand' : 'text-positive' }}">{{ $availableSeats }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Upcoming departures table -->
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-line overflow-hidden">
            <div class="px-6 py-5 flex items-center justify-between border-b border-line">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg tracking-tight">Upcoming Departures</h3>
                        <p class="text-xs text-charcoal mt-0.5 font-medium">Next trips scheduled</p>
                    </div>
                </div>
                <a href="{{ route('departures.index') }}" class="text-sm font-bold text-brand hover:text-brand-hover flex items-center gap-1">
                    View all
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-charcoal border-b border-line bg-fog/50">
                            <th class="px-6 py-4 font-semibold">
                                @include('partials.sort-link', ['filter' => $filter, 'column' => 'package_name', 'label' => 'Package'])
                            </th>
                            <th class="px-6 py-4 font-semibold">
                                @include('partials.sort-link', ['filter' => $filter, 'column' => 'departure_date', 'label' => 'Departure'])
                            </th>
                            <th class="px-6 py-4 font-semibold">
                                @include('partials.sort-link', ['filter' => $filter, 'column' => 'destination', 'label' => 'Country'])
                            </th>
                            <th class="px-6 py-4 font-semibold text-right">Pax</th>
                            <th class="px-6 py-4 font-semibold text-right">Capacity</th>
                            <th class="px-6 py-4 font-semibold text-right">Available</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($upcomingDepartures as $departure)
                            <tr class="transition-colors hover:bg-fog/50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('departures.show', $departure) }}" class="font-bold text-ink hover:text-brand">
                                        {{ $departure->package->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-charcoal font-medium">{{ $departure->departure_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-charcoal font-medium">{{ $departure->package->destination }}</td>
                                <td class="px-6 py-4 text-right font-semibold">{{ $departure->registered_pax }}</td>
                                <td class="px-6 py-4 text-right text-charcoal">{{ $departure->total_seats }}</td>
                                <td class="px-6 py-4 text-right font-bold {{ $departure->available_seats <= 0 ? 'text-brand' : 'text-positive' }}">{{ $departure->available_seats }}</td>
                                <td class="px-6 py-4">
                                    @include('partials.status-badge', ['status' => $departure->status_label])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-charcoal font-medium">
                                    No upcoming departures. Create a departure first.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Trips need attention -->
        <div class="bg-white rounded-3xl shadow-sm border border-line overflow-hidden">
            <div class="px-6 py-5 border-b border-line">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-warning-soft flex items-center justify-center">
                        <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg tracking-tight">Trips Need Attention</h3>
                        <p class="text-xs text-charcoal mt-0.5 font-medium">Open trips with the most seats still available</p>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-line">
                @forelse ($needAttention as $departure)
                    <a href="{{ route('departures.show', $departure) }}" class="block px-6 py-5 transition-colors hover:bg-fog/50">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black uppercase text-sm tracking-tight">{{ $departure->package->name }}</p>
                                <p class="text-xs text-charcoal mt-1 font-medium flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $departure->departure_date->format('d M Y') }}
                                </p>
                            </div>
                            @include('partials.status-badge', ['status' => $departure->status_label])
                        </div>
                        <div class="flex items-center justify-between mt-4 text-sm">
                            <span class="text-charcoal font-medium">{{ $departure->registered_pax }} / {{ $departure->total_seats }} pax</span>
                            <span class="font-bold text-positive">{{ $departure->available_seats }} seats available</span>
                        </div>
                        <div class="mt-3 h-2 bg-fog rounded-full overflow-hidden">
                            @php
                                $percent = $departure->total_seats > 0 ? round(($departure->registered_pax / $departure->total_seats) * 100) : 0;
                            @endphp
                            <div class="h-full rounded-full bg-brand" style="width: {{ min(100, $percent) }}%"></div>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-12 text-center text-charcoal font-medium text-sm">
                        No open trips with available seats right now.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
