@extends('layouts.app')

@section('title', 'Participants')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight leading-none">Participants</h2>
            <p class="text-sm text-charcoal mt-2">All customer/participant registrations.</p>
        </div>
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route('participants.index') }}" class="bg-white rounded-3xl shadow-sm border border-line p-5 mb-6 flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[240px]">
            <label for="search" class="block text-xs font-semibold text-charcoal mb-2">Search</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-charcoal" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search by package, participant name, or phone..."
                    class="w-full rounded-full border border-line bg-white pl-12 pr-5 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
            </div>
        </div>
        <button type="submit"
                class="bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
            Search
        </button>
        @if ($search)
            <a href="{{ route('participants.index') }}" class="text-sm font-semibold text-charcoal hover:text-ink px-2 py-3">Clear</a>
        @endif
    </form>

    <div class="bg-white rounded-3xl shadow-sm border border-line overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-charcoal border-b border-line bg-fog/50">
                        <th class="px-6 py-4 font-semibold">Name</th>
                        <th class="px-6 py-4 font-semibold">Phone</th>
                        <th class="px-6 py-4 font-semibold">Package</th>
                        <th class="px-6 py-4 font-semibold">Departure</th>
                        <th class="px-6 py-4 font-semibold text-right">Pax</th>
                        <th class="px-6 py-4 font-semibold">Payment</th>
                        <th class="px-6 py-4 font-semibold">Partner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($registrations as $registration)
                        <tr class="transition-colors hover:bg-fog/50">
                            <td class="px-6 py-4 font-bold">{{ $registration->name }}</td>
                            <td class="px-6 py-4 text-charcoal font-medium">{{ $registration->phone }}</td>
                            <td class="px-6 py-4 text-charcoal font-medium">{{ $registration->departure->package->name }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('departures.show', $registration->departure) }}"
                                   class="text-brand hover:text-brand-hover font-bold">
                                    {{ $registration->departure->departure_date->format('d M Y') }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-right font-bold">{{ $registration->pax }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $registration->payment_color }}">
                                    {{ $registration->payment_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($registration->need_partner)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-warning-soft text-warning">
                                        {{ $registration->partner_label }}
                                    </span>
                                @else
                                    <span class="text-charcoal font-medium">No</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-charcoal font-medium">
                                @if ($search)
                                    No participants matched your search.
                                @else
                                    No participants yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($registrations->hasPages())
            <div class="px-6 py-4 border-t border-line">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>
@endsection
