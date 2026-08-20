@extends('layouts.app')

@section('title', 'Hermes Update')

@section('content')
    <!-- Page header -->
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">Hermes Update</h2>
            <p class="text-sm text-charcoal mt-2">Seat changes Hermes made. Latest first · travel date and time updated.</p>
        </div>
        @if ($activities->total() > 0)
            <span class="inline-flex items-center gap-1.5 self-start sm:self-auto px-3.5 py-1.5 rounded-full bg-brand-soft text-brand text-xs font-bold flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                {{ $activities->total() }} updates
            </span>
        @endif
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-surface rounded-2xl shadow-sm border border-line px-4 sm:px-6 py-4 mb-4 flex flex-wrap items-end gap-x-4 gap-y-3">
        <div class="flex-1 min-w-[12rem]">
            <label for="package" class="block text-[11px] font-semibold text-charcoal mb-1.5">Package</label>
            <input type="text"
                   id="package"
                   name="package"
                   value="{{ $packageFilter }}"
                   placeholder="Package name..."
                   autocomplete="off"
                   class="w-full rounded-xl border border-line bg-surface px-3 py-2 text-xs font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
        </div>
        <div>
            <label for="month" class="block text-[11px] font-semibold text-charcoal mb-1.5">Month</label>
            <select name="month" id="month" class="rounded-xl border border-line bg-surface px-3 py-2 text-xs font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all min-w-[8.5rem]">
                <option value="">All Months</option>
                @foreach (\App\Support\TripListFilter::months() as $num => $label)
                    <option value="{{ $num }}" @selected($monthFilter === $num)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-3 pb-0.5">
            <button type="submit"
                    class="bg-brand hover:bg-brand-hover text-white text-xs font-bold rounded-full px-5 py-2 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
                Filter
            </button>
            <a href="{{ route('hermes.updates') }}" class="text-xs font-semibold text-charcoal hover:text-ink py-2">Reset</a>
        </div>
    </form>

    <!-- Activity feed -->
    <div class="bg-surface rounded-3xl shadow-sm border border-line overflow-hidden">
        <div class="divide-y divide-line">
            @forelse ($activities as $activity)
                @php
                    $isAdd = $activity->seat_delta > 0;
                @endphp
                <div class="flex items-center gap-3 sm:gap-4 px-4 sm:px-6 py-4 hover:bg-fog/60 transition-colors duration-150">
                    <!-- Direction icon chip -->
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl flex-shrink-0 flex items-center justify-center {{ $isAdd ? 'bg-positive-soft text-positive' : 'bg-brand-soft text-brand' }}">
                        @if ($isAdd)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                            </svg>
                        @endif
                    </div>

                    <!-- Main info -->
                    <div class="min-w-0 flex-1">
                        @if ($activity->departure_id)
                            <a href="{{ route('departures.show', $activity->departure_id) }}"
                               class="font-bold text-sm sm:text-base text-ink hover:text-brand transition-colors tracking-tight truncate block">
                                {{ $activity->package_name }}
                            </a>
                        @else
                            <p class="font-bold text-sm sm:text-base text-ink tracking-tight truncate">{{ $activity->package_name }}</p>
                        @endif
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-1 text-xs">
                            <span class="text-charcoal font-semibold inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Trip {{ $activity->departure_date->format('D, j M Y') }}
                            </span>
                            <span class="text-charcoal/70 font-medium" title="{{ $activity->updated_at_label }}">
                                {{ $activity->created_at->timezone('Asia/Kuala_Lumpur')->diffForHumans() }}
                            </span>
                        </div>
                        @if ($activity->activity_type || $activity->actor_name || $activity->activity_note)
                            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 mt-1.5 text-xs">
                                @if ($activity->activity_type)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-brand-soft text-brand font-bold whitespace-nowrap">
                                        {{ $activity->activity_type_label }}
                                    </span>
                                @endif
                                @if ($activity->actor_name)
                                    <span class="text-charcoal font-semibold inline-flex items-center gap-1 whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $activity->actor_name }}
                                    </span>
                                @endif
                                @if ($activity->activity_note)
                                    <span class="text-charcoal/70 font-medium truncate">{{ $activity->activity_note }}</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Seat delta badge -->
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-black flex-shrink-0 {{ $isAdd ? 'bg-positive-soft text-positive' : 'bg-brand-soft text-brand' }}">
                        {{ $activity->seat_change_label }}
                    </span>
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <div class="w-14 h-14 rounded-3xl bg-brand-soft flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <p class="font-bold text-ink text-sm">No seat updates yet</p>
                    <p class="text-charcoal font-medium text-sm mt-1">Changes Hermes makes will show up here.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if ($activities->hasPages())
        <div class="mt-4">
            {{ $activities->links() }}
        </div>
    @endif
@endsection
