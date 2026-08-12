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

    <!-- Summary cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Upcoming Trips</p>
            </div>
            <p class="text-3xl font-black leading-none">{{ $upcomingTrips }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Total Capacity</p>
            </div>
            <p class="text-3xl font-black leading-none">{{ $totalCapacity }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-positive-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-positive" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Seats Available</p>
            </div>
            <p class="text-3xl font-black leading-none {{ $availableSeats <= 0 ? 'text-brand' : 'text-positive' }}">{{ $availableSeats }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-warning-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Almost Full</p>
            </div>
            <p class="text-3xl font-black leading-none {{ $almostFullTrips > 0 ? 'text-warning' : '' }}">{{ $almostFullTrips }}</p>
        </div>
    </div>

    <!-- Upcoming Trips — main focus -->
    <div class="bg-white rounded-2xl shadow-sm border border-line overflow-hidden mb-6">
        <div class="px-6 py-5 border-b border-line flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg tracking-tight">Upcoming Trips</h3>
                    <p class="text-xs text-charcoal mt-0.5 font-medium">{{ $upcomingTrips }} departures scheduled</p>
                </div>
            </div>
            <a href="{{ route('departures.index') }}" class="text-sm font-bold text-brand hover:text-brand-hover flex items-center gap-1">
                View all
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Filter toolbar -->
        <div class="px-6 py-4 border-b border-line bg-fog/40">
            @include('partials.trip-filters', ['filter' => $filter])
        </div>

        <!-- Trip card-rows -->
        <div class="p-4 space-y-3">
            @forelse ($upcomingDepartures as $departure)
                @include('partials.trip-card', ['departure' => $departure])
            @empty
                <div class="px-6 py-12 text-center text-charcoal font-medium">
                    No upcoming departures. Create a departure first.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Trips need attention -->
    <div class="bg-white rounded-2xl shadow-sm border border-line overflow-hidden">
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
                    <div class="mt-4">
                        @include('partials.capacity-bar', ['departure' => $departure])
                    </div>
                </a>
            @empty
                <div class="px-6 py-12 text-center text-charcoal font-medium text-sm">
                    No open trips with available seats right now.
                </div>
            @endforelse
        </div>
    </div>
@endsection
