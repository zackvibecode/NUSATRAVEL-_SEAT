@extends('layouts.app')

@section('title', 'Calendar')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight leading-none">Calendar</h2>
            <p class="text-sm text-charcoal mt-2">Monthly view of all departures.</p>
        </div>
        <form method="GET" action="{{ route('calendar.index') }}" class="flex items-end gap-3">
            <div>
                <label for="month" class="block text-xs font-semibold text-charcoal mb-2">Month</label>
                <select name="month" id="month"
                        class="rounded-full border border-line bg-white px-4 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
                    @foreach ($months as $num => $label)
                        <option value="{{ $num }}" @selected($month === $num)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="year" class="block text-xs font-semibold text-charcoal mb-2">Year</label>
                <select name="year" id="year"
                        class="rounded-full border border-line bg-white px-4 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
                    @for ($y = now()->year - 1; $y <= now()->year + 2; $y++)
                        <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit"
                    class="bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-2.5 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
                View
            </button>
        </form>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-line overflow-hidden">
        <div class="px-6 py-5 border-b border-line flex items-center justify-between">
            <h3 class="font-bold text-lg tracking-tight">{{ $monthLabel }} {{ $year }}</h3>
            <div class="flex items-center gap-4 text-xs font-medium text-charcoal">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-brand"></span> Departure
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-positive"></span> Has seats
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-warning"></span> Almost full
                </span>
            </div>
        </div>

        <div class="grid grid-cols-7 text-center text-xs font-semibold text-charcoal border-b border-line bg-fog/50">
            <div class="py-3">Sun</div>
            <div class="py-3">Mon</div>
            <div class="py-3">Tue</div>
            <div class="py-3">Wed</div>
            <div class="py-3">Thu</div>
            <div class="py-3">Fri</div>
            <div class="py-3">Sat</div>
        </div>

        @foreach ($weeks as $week)
            <div class="grid grid-cols-7 border-b border-line last:border-b-0">
                @foreach ($week as $cell)
                    <div class="min-h-[100px] border-r border-line last:border-r-0 p-2 {{ $cell ? 'bg-white' : 'bg-fog/30' }}">
                        @if ($cell)
                            <div class="text-xs font-bold text-charcoal mb-1">{{ $cell['day'] }}</div>
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
