@extends('layouts.app')

@section('title', 'Attention Trips')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">Attention Trips</h2>
            <p class="text-sm text-charcoal mt-2">Open trips that still need more registrations, sorted by earliest month first.</p>
        </div>
        <a href="{{ route('attention-trips.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-charcoal hover:text-ink">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
    </div>

    @forelse ($groupedDepartures as $monthLabel => $departures)
        <div class="mb-8">
            <!-- Month header -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-base tracking-tight">{{ $monthLabel }}</h3>
                <span class="text-xs font-bold text-charcoal bg-fog rounded-full px-3 py-1">{{ $departures->count() }} trips</span>
            </div>

            <!-- Trip cards for this month -->
            <div class="space-y-3">
                @foreach ($departures as $departure)
                    @include('partials.trip-card', ['departure' => $departure])
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-line px-6 py-12 text-center text-charcoal font-medium">
            No trips need attention right now.
        </div>
    @endforelse
@endsection
