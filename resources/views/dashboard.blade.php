@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Page header -->
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
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

    <!-- KPI cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4">
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
            <p class="text-xs text-charcoal/70 mt-1.5 font-medium">{{ $registeredPax }} pax booked</p>
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
            <p class="text-xs text-charcoal/70 mt-1.5 font-medium">{{ $overallOccupancy }}% occupied</p>
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
            <p class="text-xs text-charcoal/70 mt-1.5 font-medium">{{ $availableSeats <= 0 ? 'All trips full' : 'across all trips' }}</p>
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
            <p class="text-xs text-charcoal/70 mt-1.5 font-medium">{{ $attentionCount }} need attention</p>
        </div>
    </div>

    <!-- 6-month revenue & pax trend — SVG line chart -->
    <div class="bg-white rounded-xl shadow-sm border border-line overflow-hidden mb-4">
        <div class="px-4 sm:px-6 py-4 border-b border-line flex items-center justify-between">
            <div>
                <h3 class="font-bold text-base sm:text-lg tracking-tight">Revenue &amp; Pax Trend</h3>
                <p class="text-xs text-charcoal mt-0.5 font-medium">Last 6 months · recognised: paid in full, deposit at 50%</p>
            </div>
            <div class="hidden sm:flex items-center gap-4 text-xs font-semibold">
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-brand rounded-full inline-block"></span>Revenue</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-charcoal rounded-full inline-block" style="opacity:.5"></span>Pax</span>
            </div>
        </div>
        <div class="p-4 sm:p-6">
            @php
                $hasData = $trendData->contains(fn ($t) => $t['revenue'] > 0 || $t['pax'] > 0);
                $maxRevenue = max(1, $trendData->max(fn ($t) => $t['revenue']));
                $maxPax = max(1, $trendData->max(fn ($t) => $t['pax']));
                $chartW = 600;
                $chartH = 180;
                $padL = 36;
                $padR = 12;
                $padT = 8;
                $padB = 24;
                $plotW = $chartW - $padL - $padR;
                $plotH = $chartH - $padT - $padB;
                $points = $trendData->values()->map(function ($t, $i) use ($trendData, $maxRevenue, $maxPax, $plotW, $plotH, $padL, $padT) {
                    $count = $trendData->count();
                    $x = $count > 1 ? $padL + ($plotW / ($count - 1)) * $i : $padL + $plotW / 2;
                    $revY = $padT + $plotH - ($plotH * ($t['revenue'] / $maxRevenue));
                    $paxY = $padT + $plotH - ($plotH * ($t['pax'] / $maxPax));
                    return ['x' => round($x, 1), 'revY' => round($revY, 1), 'paxY' => round($paxY, 1), 'label' => $t['label'], 'revenue' => $t['revenue'], 'pax' => $t['pax']];
                });
                $revPath = $points->map(fn ($p, $i) => ($i === 0 ? 'M' : 'L').$p['x'].','.$p['revY'])->implode(' ');
                $paxPath = $points->map(fn ($p, $i) => ($i === 0 ? 'M' : 'L').$p['x'].','.$p['paxY'])->implode(' ');
                $gridLines = [0.25, 0.5, 0.75];
            @endphp
            @if ($hasData)
                <div class="overflow-x-auto">
                    <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="w-full min-w-[400px]" role="img" aria-label="Line chart of revenue and pax over the last 6 months. {{ $points->map(fn ($p) => $p['label'].': RM '.number_format($p['revenue'], 0).', '.$p['pax'].' pax')->implode('. ') }}">
                        <title>Revenue and Pax Trend</title>
                        <desc>6-month line chart showing recognised revenue and registered pax</desc>
                        @foreach ($gridLines as $g)
                            <line x1="{{ $padL }}" y1="{{ $padT + $plotH * (1 - $g) }}" x2="{{ $chartW - $padR }}" y2="{{ $padT + $plotH * (1 - $g) }}" stroke="#e8e4e2" stroke-width="1" stroke-dasharray="3 3"/>
                        @endforeach
                        <line x1="{{ $padL }}" y1="{{ $padT + $plotH }}" x2="{{ $chartW - $padR }}" y2="{{ $padT + $plotH }}" stroke="#e8e4e2" stroke-width="1.5"/>
                        @foreach ($points as $p)
                            <text x="{{ $p['x'] }}" y="{{ $chartH - 4 }}" text-anchor="middle" font-size="11" fill="#454245" font-weight="600">{{ $p['label'] }}</text>
                        @endforeach
                        <path d="{{ $paxPath }}" fill="none" stroke="#454245" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/>
                        @foreach ($points as $p)
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['paxY'] }}" r="4" fill="#454245" opacity="0.5">
                                <title>{{ $p['label'] }}: {{ $p['pax'] }} pax</title>
                            </circle>
                        @endforeach
                        <path d="{{ $revPath }}" fill="none" stroke="#e4002b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        @foreach ($points as $p)
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['revY'] }}" r="4" fill="#e4002b">
                                <title>{{ $p['label'] }}: RM {{ number_format($p['revenue'], 0) }}</title>
                            </circle>
                            @if ($p['revenue'] > 0)
                                <text x="{{ $p['x'] }}" y="{{ $p['revY'] - 10 }}" text-anchor="middle" font-size="10" font-weight="700" fill="#e4002b">{{ number_format($p['revenue'] / 1000, 1) }}k</text>
                            @endif
                        @endforeach
                    </svg>
                </div>
                <div class="sm:hidden mt-3 flex items-center gap-4 text-xs font-semibold">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-brand rounded-full inline-block"></span>Revenue</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-charcoal rounded-full inline-block" style="opacity:.5"></span>Pax</span>
                </div>
            @else
                <div class="py-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-brand-soft flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-ink">No trend data yet</p>
                    <p class="text-xs text-charcoal mt-1 font-medium">Charts appear once departures have registrations.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Two-column: Hermes + Attention -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <!-- Hermes seat updates -->
        <div class="bg-white rounded-xl shadow-sm border border-line overflow-hidden">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-line flex items-center justify-between gap-3">
                <h3 class="font-bold text-base tracking-tight">Hermes Update</h3>
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

        <!-- Attention trips -->
        <a href="{{ route('dashboard.attention-trips') }}"
           class="block bg-white rounded-xl shadow-sm border border-line hover:shadow-md hover:border-brand/30 transition-all duration-150 group">
            <div class="px-4 sm:px-6 py-4 sm:py-5 flex items-center justify-between h-full">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-warning-soft flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-warning" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-base sm:text-lg tracking-tight group-hover:text-brand transition-colors">Trips Need Attention</h3>
                        <p class="text-xs sm:text-sm text-charcoal mt-0.5 font-medium truncate">
                            {{ $attentionCount }} open trips · sorted by earliest month
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
    </div>

    <!-- Upcoming Trips -->
    <div class="bg-white rounded-xl shadow-sm border border-line overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-line flex items-center justify-between">
            <div>
                <h3 class="font-bold text-base sm:text-lg tracking-tight">Upcoming Trips</h3>
                <p class="text-xs text-charcoal mt-0.5 font-medium">Next 5 departures scheduled</p>
            </div>
            <a href="{{ route('departures.index') }}" class="text-sm font-bold text-brand hover:text-brand-hover flex items-center gap-1 flex-shrink-0">
                View all
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="px-4 sm:px-6 py-4 border-b border-line bg-fog/40">
            @include('partials.trip-filters', ['filter' => $filter])
        </div>
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
@endsection
