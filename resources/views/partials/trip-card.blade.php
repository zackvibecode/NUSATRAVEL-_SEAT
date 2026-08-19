@php
    /** @var \App\Models\Departure $departure */
    $status = $departure->status_label;
    $isCancelled = $status === 'cancelled';
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
@endphp

<div class="group relative bg-surface {{ $isCancelled ? 'bg-brand-soft border-brand' : 'border-line hover:border-brand/40' }} border rounded-xl {{ $isCancelled ? '' : 'transition-all duration-150' }}">
    @if ($isCancelled)
        <div class="absolute top-0 left-0 right-0 h-1 bg-brand rounded-t-xl"></div>
    @endif
    <div class="px-4 py-3.5 sm:px-5">
        <!-- Top row: package name + status -->
        <div class="flex items-start justify-between gap-3 mb-2.5">
            <div class="min-w-0">
                <a href="{{ route('departures.show', $departure) }}"
                   class="font-semibold text-sm sm:text-base tracking-tight {{ $isCancelled ? 'text-brand-ink line-through decoration-brand/60' : 'text-ink group-hover:text-brand' }} transition-colors block">
                    {{ $departure->package->name }}
                </a>
                <p class="text-xs {{ $isCancelled ? 'text-brand-ink/70' : 'text-charcoal' }} font-medium mt-0.5">
                    {{ $departure->package->destination }}
                    @if ($departure->airline)
                        <span class="text-line mx-1">·</span>{{ $departure->airline }}
                    @endif
                </p>
            </div>
            @include('partials.status-badge', ['status' => $status])
        </div>

        <!-- Middle row: date + capacity + action (grid) -->
        <div class="grid grid-cols-2 sm:grid-cols-[auto_1fr_auto] gap-3 sm:gap-6 items-center">
            <!-- Departure date -->
            <div>
                <p class="text-[10px] font-semibold {{ $isCancelled ? 'text-brand-ink/60' : 'text-charcoal' }} uppercase tracking-wider mb-0.5">Departure</p>
                <p class="text-sm font-medium {{ $isCancelled ? 'text-brand-ink' : 'text-ink' }} whitespace-nowrap">{{ $departure->departure_date->format('d M Y') }}</p>
            </div>

            <!-- Capacity -->
            <div>
                <div class="flex items-baseline justify-between gap-2 mb-1">
                    <p class="text-[10px] font-semibold {{ $isCancelled ? 'text-brand-ink/60' : 'text-charcoal' }} uppercase tracking-wider">Capacity</p>
                    <p class="text-xs font-semibold {{ $isCancelled ? 'text-brand' : ($departure->available_seats <= 0 ? 'text-brand' : ($status === 'almost_full' ? 'text-warning' : 'text-positive')) }}">
                        {{ $departure->available_seats }} left
                    </p>
                </div>
                <div class="flex items-baseline gap-1.5 mb-1.5">
                    <span class="text-sm font-semibold {{ $isCancelled ? 'text-brand-ink' : 'text-ink' }}">{{ $departure->registered_pax }}</span>
                    <span class="text-xs {{ $isCancelled ? 'text-brand-ink/70' : 'text-charcoal' }} font-medium">/ {{ $departure->total_seats }} booked</span>
                </div>
                @php
                    $percent = $departure->total_seats > 0 ? round(($departure->registered_pax / $departure->total_seats) * 100) : 0;
                    $barColor = match ($status) {
                        'full', 'departed' => 'bg-brand',
                        'cancelled' => 'bg-brand opacity-50',
                        'almost_full' => 'bg-warning',
                        default => 'bg-positive',
                    };
                @endphp
                <div class="h-1.5 {{ $isCancelled ? 'bg-brand/20' : 'bg-fog' }} rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $barColor }} transition-all duration-500" style="width: {{ min(100, $percent) }}%"></div>
                </div>
            </div>

            <!-- Action -->
            <div class="sm:justify-self-end flex items-center gap-2">
                <a href="{{ route('departures.show', $departure) }}"
                   class="inline-flex items-center gap-1 border {{ $isCancelled ? 'border-brand/40 text-brand/70 hover:bg-brand hover:text-white hover:border-brand' : 'border-brand text-brand hover:bg-brand hover:text-white' }} text-xs font-semibold rounded-full px-3.5 py-1.5 transition-colors duration-150 whitespace-nowrap">
                    {{ $isCancelled ? 'View' : 'Manage' }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @if ($isAdmin)
                    <button type="button"
                            class="inline-flex items-center justify-center w-7 h-7 rounded-full text-red-500 hover:text-red-600 hover:bg-red-50 border border-transparent hover:border-red-200 transition-colors duration-150"
                            title="Delete trip"
                            aria-label="Delete trip {{ $departure->package->name }}"
                            data-delete-trip
                            data-delete-url="{{ route('departures.destroy', $departure) }}"
                            data-trip-label="{{ $departure->package->name }} — {{ $departure->departure_date->format('d M Y') }}"
                            data-trip-registrations="{{ $departure->registered_pax }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
