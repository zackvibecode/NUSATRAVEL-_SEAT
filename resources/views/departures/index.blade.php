@extends('layouts.app')

@section('title', 'Departures')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-none">Trips / Departures</h2>
            <p class="text-sm text-charcoal mt-2">One specific travel date under a package, with its own seat capacity.</p>
        </div>
        <a href="{{ route('departures.create') }}"
           class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Departure
        </a>
    </div>

    <!-- Filter toolbar -->
    <div class="bg-white rounded-xl shadow-sm border border-line p-4 sm:p-5 mb-4">
        @include('partials.trip-filters', ['filter' => $filter])
    </div>

    <!-- Trip card-rows -->
    <div class="space-y-3">
        @forelse ($departures as $departure)
            @include('partials.trip-card', ['departure' => $departure])
        @empty
            <div class="bg-white rounded-xl border border-line px-6 py-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-brand-soft flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-ink">No departures found</p>
                <p class="text-xs text-charcoal mt-1 font-medium">
                    @if ($filter->isActive())
                        Try clearing filters or adjusting your search{{ $filter->search ? ' for "' . e($filter->search) . '"' : '' }}.
                    @else
                        Create your first departure to get started.
                    @endif
                </p>
                @if (! $filter->isActive())
                    <a href="{{ route('departures.create') }}" class="inline-flex items-center gap-2 mt-4 bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-5 py-2.5 transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Departure
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    @if ($departures->hasPages())
        <div class="bg-white rounded-xl shadow-sm border border-line px-6 py-4 mt-4">
            {{ $departures->links() }}
        </div>
    @endif
@endsection
