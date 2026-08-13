@php
    /** @var \App\Models\Departure $departure */
    $status = $departure->status_label;
@endphp

<div class="group relative bg-white border border-line rounded-xl hover:border-brand/40 hover:shadow-md transition-all duration-150">
    <div class="p-4 sm:p-5">
        <!-- Top row: package name + status -->
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="min-w-0">
                <a href="{{ route('departures.show', $departure) }}"
                   class="font-black text-base sm:text-lg tracking-tight text-ink group-hover:text-brand transition-colors block">
                    {{ $departure->package->name }}
                </a>
                <p class="text-xs text-charcoal font-medium mt-0.5">
                    {{ $departure->package->destination }}
                    @if ($departure->airline)
                        <span class="text-line mx-1">·</span>{{ $departure->airline }}
                    @endif
                </p>
            </div>
            @include('partials.status-badge', ['status' => $status])
        </div>

        <!-- Middle row: date + capacity + price (grid) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 items-center">
            <!-- Departure date -->
            <div>
                <p class="text-[10px] font-semibold text-charcoal uppercase tracking-wider mb-1">Departure</p>
                <p class="text-sm font-bold text-ink whitespace-nowrap">{{ $departure->departure_date->format('d M Y') }}</p>
            </div>

            <!-- Capacity -->
            <div class="col-span-2 sm:col-span-2">
                <div class="flex items-baseline justify-between gap-2 mb-1">
                    <p class="text-[10px] font-semibold text-charcoal uppercase tracking-wider">Capacity</p>
                    <p class="text-xs font-bold {{ $departure->available_seats <= 0 ? 'text-brand' : ($status === 'almost_full' ? 'text-warning' : 'text-positive') }}">
                        {{ $departure->available_seats }} left
                    </p>
                </div>
                <div class="flex items-baseline gap-1.5 mb-1.5">
                    <span class="text-sm font-black text-ink">{{ $departure->registered_pax }}</span>
                    <span class="text-xs text-charcoal font-medium">/ {{ $departure->total_seats }} booked</span>
                </div>
                @php
                    $percent = $departure->total_seats > 0 ? round(($departure->registered_pax / $departure->total_seats) * 100) : 0;
                    $barColor = match ($status) {
                        'full', 'departed', 'cancelled' => 'bg-brand',
                        'almost_full' => 'bg-warning',
                        default => 'bg-positive',
                    };
                @endphp
                <div class="h-2 bg-fog rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $barColor }} transition-all duration-500" style="width: {{ min(100, $percent) }}%"></div>
                </div>
            </div>

            <!-- Price + action -->
            <div class="flex flex-col sm:items-end gap-2">
                @if ($departure->price)
                    <p class="text-sm font-black text-ink">RM {{ number_format($departure->price, 0) }}<span class="text-xs font-medium text-charcoal"> /pax</span></p>
                @else
                    <span class="hidden sm:block">&nbsp;</span>
                @endif
                <a href="{{ route('departures.show', $departure) }}"
                   class="inline-flex items-center justify-center gap-1.5 bg-brand hover:bg-brand-hover text-white text-xs font-bold rounded-full px-4 py-2 transition-all duration-150 hover:scale-[1.03] shadow-sm whitespace-nowrap">
                    Manage
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
