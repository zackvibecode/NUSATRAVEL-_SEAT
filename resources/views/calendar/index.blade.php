@extends('layouts.app')

@section('title', 'Calendar')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">Calendar</h2>
            <p class="text-sm text-charcoal mt-2">Monthly view of all departures.</p>
        </div>
    </div>

    <div class="bg-surface rounded-lg border border-line overflow-hidden">
        <!-- Header with prev/next navigation -->
        <div class="px-4 sm:px-6 py-4 border-b border-line flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('calendar.index', ['month' => $prevMonth, 'year' => $prevYear]) }}"
                   class="inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-fog hover:bg-brand-soft text-charcoal hover:text-brand transition-all duration-150"
                   title="Previous month">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <a href="{{ route('calendar.index', ['month' => $nextMonth, 'year' => $nextYear]) }}"
                   class="inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-fog hover:bg-brand-soft text-charcoal hover:text-brand transition-all duration-150"
                   title="Next month">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <h3 class="font-bold text-base sm:text-lg tracking-tight ml-1 sm:ml-2">{{ $monthLabel }} {{ $year }}</h3>
                @if (! $isCurrentMonth)
                    <a href="{{ route('calendar.index') }}"
                       class="text-xs font-bold text-brand hover:text-brand-hover px-3 py-1.5 rounded-full bg-brand-soft hover:bg-brand hover:text-white transition-all duration-150">
                        Today
                    </a>
                @endif
            </div>
            <div class="hidden sm:flex items-center gap-4 text-xs font-medium text-charcoal">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-brand"></span> Full
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-warning"></span> Almost full
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-positive"></span> Has seats
                </span>
            </div>
        </div>

        <!-- Day headers -->
        <div class="grid grid-cols-7 text-center text-xs font-semibold text-charcoal border-b border-line bg-fog/50">
            <div class="py-2 sm:py-3 text-[10px] sm:text-xs">Sun</div>
            <div class="py-2 sm:py-3 text-[10px] sm:text-xs">Mon</div>
            <div class="py-2 sm:py-3 text-[10px] sm:text-xs">Tue</div>
            <div class="py-2 sm:py-3 text-[10px] sm:text-xs">Wed</div>
            <div class="py-2 sm:py-3 text-[10px] sm:text-xs">Thu</div>
            <div class="py-2 sm:py-3 text-[10px] sm:text-xs">Fri</div>
            <div class="py-2 sm:py-3 text-[10px] sm:text-xs">Sat</div>
        </div>

        <!-- Calendar grid -->
        @foreach ($weeks as $week)
            <div class="grid grid-cols-7 border-b border-line last:border-b-0">
                @foreach ($week as $cell)
                    @php
                        $isToday = $cell && $cell['date']->isToday();
                    @endphp
                    <div class="min-h-[80px] sm:min-h-[100px] border-r border-line last:border-r-0 p-1 sm:p-2 {{ $cell ? 'bg-surface' : 'bg-fog/30' }} {{ $isToday ? 'ring-2 ring-brand ring-inset' : '' }}">
                        @if ($cell)
                            <div class="text-xs font-bold {{ $isToday ? 'text-brand' : 'text-charcoal' }} mb-1 flex items-center justify-between">
                                <span>{{ $cell['day'] }}</span>
                                @if ($isToday)
                                    <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
                                @endif
                            </div>
                            <div class="space-y-1">
                                @foreach ($cell['departures'] as $departure)
                                    @php
                                        $color = $departure->available_seats <= 0 ? 'bg-brand text-white' : ($departure->available_seats <= 5 ? 'bg-warning-soft text-warning' : 'bg-positive-soft text-positive');
                                    @endphp
                                    <a href="{{ route('departures.show', $departure) }}"
                                       class="block text-[10px] font-semibold px-2 py-1 rounded-lg {{ $color }} truncate hover:opacity-80 transition-opacity"
                                       title="{{ $departure->package->name }} — {{ $departure->registered_pax }}/{{ $departure->total_seats }} pax">
                                        {{ Str::limit($departure->package->name, 12) }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
