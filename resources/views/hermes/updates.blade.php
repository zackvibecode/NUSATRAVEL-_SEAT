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

    {{-- "What's new" popup: customer names new / updated / cancelled since last visit --}}
    @if ($freshActivities->isNotEmpty())
        <div id="whatsNewModal"
             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-ink/40 backdrop-blur-[2px]"
             role="dialog"
             aria-modal="true"
             aria-labelledby="whatsNewTitle">
            <div class="bg-surface rounded-3xl shadow-xl border border-line w-full max-w-lg max-h-[85vh] flex flex-col">
                {{-- Header --}}
                <div class="px-6 sm:px-8 pt-6 sm:pt-8 pb-4 border-b border-line">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-brand-soft text-brand flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 id="whatsNewTitle" class="text-lg font-semibold tracking-tight text-ink">
                                Apa yang baharu sejak anda buka kali terakhir
                            </h3>
                            <p class="text-xs text-charcoal mt-1 font-medium">
                                @if ($seenAtLabel)
                                    Sejak {{ $seenAtLabel }}
                                @else
                                    Perubahan terbaru Hermes
                                @endif
                                · {{ $freshActivities->count() }} kemaskini customer
                            </p>
                        </div>
                        <button type="button"
                                id="whatsNewClose"
                                class="flex-shrink-0 -mr-2 -mt-2 p-2 rounded-full text-charcoal hover:bg-fog hover:text-ink transition-colors"
                                aria-label="Tutup">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Grouped customer lists --}}
                <div class="px-6 sm:px-8 py-5 overflow-y-auto flex-1">
                    @php
                        $grouped = $freshActivities->groupBy('activity_type');
                        $groups = [
                            'registration_created'  => ['label' => 'Baharu masuk', 'icon' => 'plus', 'classes' => 'bg-positive-soft text-positive'],
                            'registration_updated'  => ['label' => 'Pax / detail diubah', 'icon' => 'edit', 'classes' => 'bg-warning-soft text-warning'],
                            'registration_deleted'  => ['label' => 'Cancel / dibatalkan', 'icon' => 'minus', 'classes' => 'bg-red-50 text-red-600'],
                        ];
                    @endphp

                    @foreach ($groups as $type => $meta)
                        @if (isset($grouped[$type]) && $grouped[$type]->isNotEmpty())
                            @php $rows = $grouped[$type]; @endphp
                            <div class="mb-5 last:mb-0">
                                <div class="flex items-center gap-2 mb-2.5">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg {{ $meta['classes'] }}">
                                        @if ($meta['icon'] === 'plus')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        @elseif ($meta['icon'] === 'edit')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                                        @endif
                                    </span>
                                    <h4 class="text-sm font-bold text-ink">{{ $meta['label'] }}</h4>
                                    <span class="text-xs font-bold text-charcoal/60">{{ $rows->count() }}</span>
                                </div>
                                <ul class="space-y-1.5 ml-8">
                                    @foreach ($rows as $row)
                                        <li class="flex items-start gap-2 text-sm">
                                            <span class="font-semibold text-ink truncate">{{ $row->actor_name ?: '(tiada nama)' }}</span>
                                            <span class="text-charcoal/60 text-xs whitespace-nowrap">·</span>
                                            <span class="text-xs text-charcoal truncate">{{ $row->package_name }}</span>
                                            @if ($row->activity_note)
                                                <span class="text-xs text-charcoal/70 font-medium truncate">— {{ $row->activity_note }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Footer --}}
                <div class="px-6 sm:px-8 py-4 border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <button type="button"
                            id="whatsNewDismiss"
                            class="inline-flex justify-center items-center rounded-full px-5 py-2.5 text-sm font-bold text-white bg-brand hover:bg-brand-hover transition-colors">
                        Ok, tengok sudah
                    </button>
                </div>
            </div>
        </div>

        <script>
            (function () {
                const modal = document.getElementById('whatsNewModal');
                if (!modal) return;

                function openModal() {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.style.overflow = 'hidden';
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.style.overflow = '';
                }

                // Auto-open on page load (robust whether script runs before or after DOM ready)
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', openModal);
                } else {
                    openModal();
                }

                document.getElementById('whatsNewClose')?.addEventListener('click', closeModal);
                document.getElementById('whatsNewDismiss')?.addEventListener('click', closeModal);
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) closeModal();
                });
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
                });
            })();
        </script>
    @endif
@endsection
