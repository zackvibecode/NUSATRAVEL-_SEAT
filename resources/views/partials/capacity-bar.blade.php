@php
    /** @var \App\Models\Departure $departure */
    $booked = $departure->registered_pax;
    $total = $departure->total_seats;
    $available = $departure->available_seats;
    $percent = $total > 0 ? round(($booked / $total) * 100) : 0;

    // Visual tone derived from the SAME status logic the backend already exposes
    $status = $departure->status_label;
    $barColor = match ($status) {
        'full', 'departed', 'cancelled' => 'bg-brand',
        'almost_full' => 'bg-warning',
        default => 'bg-positive',
    };
@endphp

<div class="flex items-center gap-3">
    <div class="flex-1">
        <div class="flex items-baseline justify-between gap-2">
            <p class="text-sm font-bold text-ink">
                {{ $booked }} <span class="text-charcoal font-medium">/ {{ $total }}</span>
            </p>
            <p class="text-xs font-bold {{ $available <= 0 ? 'text-brand' : ($status === 'almost_full' ? 'text-warning' : 'text-positive') }}">
                {{ $available }} left
            </p>
        </div>
        <div class="mt-1.5 h-2 bg-fog rounded-full overflow-hidden">
            <div class="h-full rounded-full {{ $barColor }} transition-all duration-500" style="width: {{ min(100, $percent) }}%"></div>
        </div>
    </div>
</div>
