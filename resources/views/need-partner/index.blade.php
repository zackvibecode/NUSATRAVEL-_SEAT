@extends('layouts.app')

@section('title', 'Need Partner')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">Need Partner</h2>
            <p class="text-sm text-charcoal mt-2">Solo travellers who need a room-sharing partner (PRD 13).</p>
        </div>
    </div>

    <!-- Summary counts -->
    <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-6">
        <div class="bg-surface rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-brand-soft flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight">Need Male Partner</p>
            </div>
            <p class="text-2xl sm:text-3xl font-black leading-none">{{ $maleCount }}</p>
        </div>
        <div class="bg-surface rounded-xl p-4 sm:p-5 shadow-sm border border-line">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-brand-soft flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider leading-tight">Need Female Partner</p>
            </div>
            <p class="text-2xl sm:text-3xl font-black leading-none">{{ $femaleCount }}</p>
        </div>
    </div>

    <!-- Auto-match suggestions -->
    @if ($matches->isNotEmpty())
        <div class="bg-surface rounded-lg border border-line p-4 sm:p-6 mb-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-positive-soft flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-positive" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-base sm:text-lg tracking-tight">Suggested Room Matches</h3>
                    <p class="text-xs text-charcoal mt-0.5 font-medium">Auto-paired solo travellers on the same trip with same gender</p>
                </div>
            </div>
            <div class="space-y-2">
                @foreach ($matches as $match)
                    <div class="flex items-center justify-between bg-fog rounded-xl px-4 py-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center -space-x-2 flex-shrink-0">
                                <span class="w-7 h-7 rounded-full bg-brand-soft text-brand font-bold text-[10px] flex items-center justify-center border-2 border-white">
                                    {{ strtoupper(substr($match['pair'][0]->name, 0, 1)) }}
                                </span>
                                <span class="w-7 h-7 rounded-full bg-brand-soft text-brand font-bold text-[10px] flex items-center justify-center border-2 border-white">
                                    {{ strtoupper(substr($match['pair'][1]->name, 0, 1)) }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-xs sm:text-sm truncate">{{ $match['pair'][0]->name }} + {{ $match['pair'][1]->name }}</p>
                                <p class="text-[10px] sm:text-xs text-charcoal mt-0.5 truncate">
                                    {{ $match['departure']->package->name }} · {{ $match['departure']->departure_date->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-positive-soft text-positive flex-shrink-0">
                            {{ ucfirst($match['type']) }} pair
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Registration cards -->
    <div class="space-y-3">
        @forelse ($registrations as $registration)
            <div class="bg-surface rounded-lg border border-line hover:shadow-md hover:border-brand/30 transition-all duration-150">
                <div class="p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-black text-base tracking-tight">{{ $registration->name }}</p>
                            <p class="text-xs text-charcoal font-medium mt-0.5">{{ $registration->departure->package->name }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-warning-soft text-warning flex-shrink-0">
                            {{ $registration->partner_label }}
                        </span>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('departures.show', $registration->departure) }}"
                           class="text-brand hover:text-brand-hover font-bold text-xs flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $registration->departure->departure_date->format('d M Y') }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-surface rounded-lg border border-line px-6 py-16 text-center">
                <div class="w-12 h-12 rounded-xl bg-fog flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-charcoal" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-charcoal font-medium text-sm">No registrations need a partner right now.</p>
            </div>
        @endforelse
    </div>

    @if ($registrations->hasPages())
        <div class="bg-surface rounded-lg border border-line px-6 py-4 mt-4">
            {{ $registrations->links() }}
        </div>
    @endif
@endsection