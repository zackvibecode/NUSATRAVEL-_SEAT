@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Page header -->
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-none">Dashboard</h2>
            <p class="text-sm text-charcoal mt-2">Track registrations and seat availability at a glance.</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-charcoal font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ now()->format('l, d F Y') }}
        </div>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-brand-soft flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight">Upcoming Trips</p>
            </div>
            <p class="text-2xl sm:text-3xl font-black leading-none">{{ $upcomingTrips }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-brand-soft flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight">Total Capacity</p>
            </div>
            <p class="text-2xl sm:text-3xl font-black leading-none">{{ $totalCapacity }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-positive-soft flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-positive" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight">Seats Available</p>
            </div>
            <p class="text-2xl sm:text-3xl font-black leading-none {{ $availableSeats <= 0 ? 'text-brand' : 'text-positive' }}">{{ $availableSeats }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-warning-soft flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-warning" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight">Almost Full</p>
            </div>
            <p class="text-2xl sm:text-3xl font-black leading-none {{ $almostFullTrips > 0 ? 'text-warning' : '' }}">{{ $almostFullTrips }}</p>
        </div>
    </div>

    <!-- Upcoming Trips — main focus -->
    <div class="bg-white rounded-xl shadow-sm border border-line overflow-hidden mb-4">
        <div class="px-4 sm:px-6 py-4 border-b border-line flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="font-bold text-base sm:text-lg tracking-tight">Upcoming Trips</h3>
                    <p class="text-xs text-charcoal mt-0.5 font-medium">Next 5 departures scheduled</p>
                </div>
            </div>
            <a href="{{ route('departures.index') }}" class="text-sm font-bold text-brand hover:text-brand-hover flex items-center gap-1 flex-shrink-0">
                View all
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Filter toolbar -->
        <div class="px-4 sm:px-6 py-4 border-b border-line bg-fog/40">
            @include('partials.trip-filters', ['filter' => $filter])
        </div>

        <!-- Trip card-rows -->
        <div class="p-3 sm:p-4 space-y-3">
            @forelse ($upcomingDepartures as $departure)
                @include('partials.trip-card', ['departure' => $departure])
            @empty
                <div class="px-6 py-12 text-center text-charcoal font-medium">
                    No upcoming departures. Create a departure first.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Attention trips button -->
    <a href="{{ route('dashboard.attention-trips') }}"
       class="block bg-white rounded-xl shadow-sm border border-line hover:shadow-md hover:border-brand/30 transition-all duration-150 group">
        <div class="px-4 sm:px-6 py-4 sm:py-5 flex items-center justify-between">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-warning-soft flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-warning" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-base sm:text-lg tracking-tight group-hover:text-brand transition-colors">Trips Need Attention</h3>
                    <p class="text-xs sm:text-sm text-charcoal mt-0.5 font-medium truncate">
                        {{ $attentionCount }} open trips · sorted by earliest month first
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                @if ($attentionCount > 0)
                    <span class="inline-flex items-center justify-center min-w-7 h-7 px-2 rounded-full bg-warning-soft text-warning text-xs font-black">
                        {{ $attentionCount }}
                    </span>
                @endif
                <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-fog group-hover:bg-brand group-hover:text-white text-charcoal flex items-center justify-center transition-all duration-150">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </div>
        </div>
    </a>
@endsection
