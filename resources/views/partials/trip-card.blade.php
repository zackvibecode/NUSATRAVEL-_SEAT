@php
    /** @var \App\Models\Departure $departure */
    $status = $departure->status_label;
@endphp

<div class="group bg-white rounded-2xl border border-line hover:border-brand/30 hover:shadow-md transition-all duration-150">
    <div class="p-5">
        <div class="flex flex-col lg:flex-row lg:items-center gap-5">
            <!-- Primary: package + meta -->
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('departures.show', $departure) }}"
                           class="font-black text-lg tracking-tight text-ink group-hover:text-brand transition-colors block truncate">
                            {{ $departure->package->name }}
                        </a>
                        <p class="text-sm text-charcoal font-medium mt-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $departure->package->destination }}
                            @if ($departure->airline)
                                <span class="text-line">·</span>
                                {{ $departure->airline }}
                            @endif
                        </p>
                    </div>
                    <div class="flex-shrink-0 lg:hidden">
                        @include('partials.status-badge', ['status' => $status])
                    </div>
                </div>
            </div>

            <!-- Departure date -->
            <div class="flex items-center gap-3 lg:w-40 lg:flex-shrink-0">
                <div class="w-10 h-10 rounded-xl bg-brand-soft flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-charcoal uppercase tracking-wider">Departure</p>
                    <p class="text-sm font-bold text-ink">{{ $departure->departure_date->format('d M Y') }}</p>
                </div>
            </div>

            <!-- Capacity -->
            <div class="lg:w-52 lg:flex-shrink-0">
                @include('partials.capacity-bar', ['departure' => $departure])
            </div>

            <!-- Price + status + action (desktop) -->
            <div class="hidden lg:flex items-center gap-5 lg:flex-shrink-0">
                @if ($departure->price)
                    <div class="text-right">
                        <p class="text-[11px] font-semibold text-charcoal uppercase tracking-wider">Price</p>
                        <p class="text-sm font-bold text-ink whitespace-nowrap">RM {{ number_format($departure->price, 0) }}</p>
                    </div>
                @endif
                @include('partials.status-badge', ['status' => $status])
                <a href="{{ route('departures.show', $departure) }}"
                   class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-5 py-2.5 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md whitespace-nowrap">
                    Manage
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <!-- Mobile action row -->
            <div class="flex lg:hidden items-center justify-between gap-3 pt-1">
                @if ($departure->price)
                    <p class="text-sm font-bold text-ink">RM {{ number_format($departure->price, 0) }} <span class="text-xs font-medium text-charcoal">/ pax</span></p>
                @else
                    <span></span>
                @endif
                <a href="{{ route('departures.show', $departure) }}"
                   class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-xs font-bold rounded-full px-4 py-2 transition-all duration-150 shadow-sm">
                    Manage Trip
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
