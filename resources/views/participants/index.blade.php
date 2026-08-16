@extends('layouts.app')

@section('title', 'Participants')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">Participants</h2>
            <p class="text-sm text-charcoal mt-2">All customer/participant registrations.</p>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-lg border border-line p-4 sm:p-5 mb-4">
        <form method="GET" action="{{ route('participants.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[280px] sm:min-w-[360px]">
                <label for="search" class="block text-[11px] font-semibold text-charcoal mb-1.5">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-charcoal" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by name, phone, or package..."
                        class="w-full rounded-xl border border-line bg-white pl-9 pr-4 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                    >
                </div>
            </div>
            <button type="submit"
                    class="bg-brand hover:bg-brand-hover text-white text-xs font-bold rounded-full px-5 py-2 transition-all duration-150 hover:scale-[1.03] shadow-sm">
                Search
            </button>
            @if ($search)
                <a href="{{ route('participants.index') }}" class="text-xs font-semibold text-charcoal hover:text-ink py-2">Clear</a>
            @endif
        </form>
    </div>

    <!-- Participant cards -->
    <div class="space-y-3">
        @forelse ($registrations as $registration)
            <div class="bg-white rounded-lg border border-line hover:shadow-md hover:border-brand/30 transition-all duration-150">
                <div class="p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="min-w-0">
                            <p class="font-black text-base tracking-tight">{{ $registration->name }}</p>
                            <p class="text-xs text-charcoal font-medium mt-0.5">
                                {{ $registration->departure->package->name }}
                                @if ($registration->phone)
                                    <span class="text-line mx-1">·</span>{{ $registration->phone }}
                                @endif
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold {{ $registration->payment_color }} flex-shrink-0">
                            {{ $registration->payment_label }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs">
                        <a href="{{ route('departures.show', $registration->departure) }}"
                           class="text-brand hover:text-brand-hover font-bold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $registration->departure->departure_date->format('d M Y') }}
                        </a>
                        <span class="text-charcoal font-semibold">{{ $registration->pax }} pax</span>
                        @if ($registration->need_partner)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-warning-soft text-warning">
                                {{ $registration->partner_label }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg border border-line px-6 py-16 text-center">
                <div class="w-12 h-12 rounded-xl bg-fog flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-charcoal" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-charcoal font-medium text-sm">
                    @if ($search)
                        No participants matched your search.
                    @else
                        No participants yet.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    @if ($registrations->hasPages())
        <div class="bg-white rounded-lg border border-line px-6 py-4 mt-4">
            {{ $registrations->links() }}
        </div>
    @endif
@endsection