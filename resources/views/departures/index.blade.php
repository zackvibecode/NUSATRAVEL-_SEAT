@extends('layouts.app')

@section('title', 'Departures')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight leading-none">Trips / Departures</h2>
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
    <div class="bg-white rounded-3xl shadow-sm border border-line p-5 sm:p-6 mb-6">
        @include('partials.trip-filters', ['filter' => $filter])
    </div>

    <!-- Trip card-rows -->
    <div class="space-y-3">
        @forelse ($departures as $departure)
            @include('partials.trip-card', ['departure' => $departure])
        @empty
            <div class="bg-white rounded-2xl border border-line px-6 py-12 text-center text-charcoal font-medium">
                @if ($filter->isActive())
                    No departures found for the selected filters{{ $filter->search ? ' or search "' . e($filter->search) . '"' : '' }}.
                @else
                    No departures found.
                @endif
            </div>
        @endforelse
    </div>

    @if ($departures->hasPages())
        <div class="bg-white rounded-2xl shadow-sm border border-line px-6 py-4 mt-4">
            {{ $departures->links() }}
        </div>
    @endif
@endsection
