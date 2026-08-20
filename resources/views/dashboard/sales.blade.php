@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Welcome header -->
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">Welcome back, {{ auth()->user()->name }}</h2>
            <p class="text-sm text-charcoal mt-2">Find a trip and check seats — registrations are handled by admin.</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-charcoal font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ now()->format('l, d F Y') }}
        </div>
    </div>

    <!-- KPI cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-4">
        <div class="bg-surface rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-md bg-brand-soft flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight">Upcoming Trips</p>
            </div>
            <p class="text-2xl font-semibold leading-none tracking-tight">{{ $upcomingTrips }}</p>
            <p class="text-xs text-charcoal/70 mt-1.5 font-medium">still open for selling</p>
        </div>
        <div class="bg-surface rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-md bg-positive-soft flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-positive" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight">Seats Available</p>
            </div>
            <p class="text-2xl font-semibold leading-none tracking-tight text-positive">{{ $availableSeats }}</p>
            <p class="text-xs text-charcoal/70 mt-1.5 font-medium">across all upcoming trips</p>
        </div>
        <div class="bg-surface rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-md bg-warning-soft flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-warning" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight">Almost Full</p>
            </div>
            <p class="text-2xl font-semibold leading-none tracking-tight {{ $almostFull > 0 ? 'text-warning' : '' }}">{{ $almostFull }}</p>
            <p class="text-xs text-charcoal/70 mt-1.5 font-medium">hurry — few seats left</p>
        </div>
    </div>

    <!-- Search / filter trips -->
    <div class="bg-surface rounded-xl border border-line p-4 sm:p-5 mb-4">
        @include('partials.trip-filters', ['filter' => $filter, 'showSearch' => true, 'showSort' => false])
    </div>

    <!-- Two-column: Nearest trips + Hermes feed -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <!-- Nearest trips -->
        <div class="lg:col-span-2 bg-surface rounded-lg border border-line overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-line flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-base tracking-tight">Next Trips</h3>
                    <p class="text-xs text-charcoal mt-0.5 font-medium">Closest departures first — view seat availability</p>
                </div>
                <a href="{{ route('departures.index') }}" class="text-sm font-bold text-brand hover:text-brand-hover flex items-center gap-1 flex-shrink-0">
                    All trips
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div class="p-3 sm:p-4 space-y-3">
                @forelse ($nearestDepartures as $departure)
                    @include('partials.trip-card', ['departure' => $departure])
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-brand-soft flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-ink">No upcoming trips found</p>
                        <p class="text-xs text-charcoal mt-1 font-medium">Try clearing the filters above.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right column: Hermes feed + Recent customers -->
        <div class="space-y-4">
            <!-- Hermes seat updates -->
            <div class="bg-surface rounded-lg border border-line overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-line flex items-center justify-between gap-3">
                    <h3 class="font-semibold text-base tracking-tight">Hermes Update</h3>
                    <a href="{{ route('hermes.updates') }}" class="text-sm font-bold text-brand hover:text-brand-hover flex items-center gap-1 flex-shrink-0">
                        View all
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                <div class="divide-y divide-line max-h-64 overflow-y-auto">
                    @forelse ($hermesSeatActivities as $activity)
                        <div class="px-4 sm:px-6 py-3 hover:bg-fog/60 transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-bold text-ink truncate">{{ $activity->package_name }}</p>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black flex-shrink-0 {{ $activity->seat_delta > 0 ? 'bg-positive-soft text-positive' : 'bg-brand-soft text-brand' }}">
                                    {{ $activity->seat_change_label }}
                                </span>
                            </div>
                            <p class="text-xs text-charcoal mt-1 font-medium">{{ $activity->departure_date->format('d M Y') }} · {{ $activity->updated_at_label }}</p>
                        </div>
                    @empty
                        <div class="px-4 sm:px-6 py-8 text-center">
                            <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-ink">No seat updates</p>
                            <p class="text-xs text-charcoal mt-0.5 font-medium">Hermes changes will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent customers -->
            <div class="bg-surface rounded-lg border border-line overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-line flex items-center justify-between gap-3">
                    <h3 class="font-semibold text-base tracking-tight">Recent Customers</h3>
                </div>
                <div class="divide-y divide-line">
                    @forelse ($recentRegistrations as $registration)
                        <a href="{{ route('departures.show', $registration->departure_id) }}" class="block px-4 sm:px-6 py-3 hover:bg-fog/60 transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-bold text-ink truncate">{{ $registration->name }}</p>
                                <span class="text-xs font-black text-charcoal flex-shrink-0">{{ $registration->pax }} pax</span>
                            </div>
                            <p class="text-xs text-charcoal mt-0.5 font-medium truncate">
                                {{ $registration->departure?->package?->name }} · {{ $registration->departure?->departure_date?->format('d M Y') }}
                            </p>
                        </a>
                    @empty
                        <div class="px-4 sm:px-6 py-8 text-center">
                            <p class="text-sm font-semibold text-ink">No customers yet</p>
                            <p class="text-xs text-charcoal mt-0.5 font-medium">Registrations you add will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
