@extends('layouts.app')

@section('title', 'Departures')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight leading-none">Trips / Departures</h2>
            <p class="text-sm text-charcoal mt-2">One specific travel date under a package, with its own seat capacity.</p>
        </div>
        <a href="{{ route('departures.create') }}"
           class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Departure
        </a>
    </div>

    @include('partials.trip-filters', ['filter' => $filter])

    <div class="bg-white rounded-3xl shadow-sm border border-line overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-charcoal border-b border-line bg-fog/50">
                        <th class="px-6 py-4 font-semibold">
                            @include('partials.sort-link', ['filter' => $filter, 'column' => 'package_name', 'label' => 'Package'])
                        </th>
                        <th class="px-6 py-4 font-semibold">
                            @include('partials.sort-link', ['filter' => $filter, 'column' => 'departure_date', 'label' => 'Departure'])
                        </th>
                        <th class="px-6 py-4 font-semibold">
                            @include('partials.sort-link', ['filter' => $filter, 'column' => 'destination', 'label' => 'Country'])
                        </th>
                        <th class="px-6 py-4 font-semibold text-right">Pax</th>
                        <th class="px-6 py-4 font-semibold text-right">Capacity</th>
                        <th class="px-6 py-4 font-semibold text-right">Available</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($departures as $departure)
                        <tr class="transition-colors hover:bg-fog/50">
                            <td class="px-6 py-4 font-bold">{{ $departure->package->name }}</td>
                            <td class="px-6 py-4 text-charcoal font-medium">{{ $departure->departure_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-charcoal font-medium">{{ $departure->package->destination }}</td>
                            <td class="px-6 py-4 text-right font-semibold">{{ $departure->registered_pax }}</td>
                            <td class="px-6 py-4 text-right text-charcoal">{{ $departure->total_seats }}</td>
                            <td class="px-6 py-4 text-right font-bold {{ $departure->available_seats <= 0 ? 'text-brand' : 'text-positive' }}">{{ $departure->available_seats }}</td>
                            <td class="px-6 py-4">
                                @include('partials.status-badge', ['status' => $departure->status_label])
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('departures.show', $departure) }}"
                                       class="inline-flex items-center gap-1.5 text-brand hover:text-brand-hover text-sm font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </a>
                                    <a href="{{ route('departures.edit', $departure) }}"
                                       class="inline-flex items-center gap-1.5 text-charcoal hover:text-ink text-sm font-semibold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-charcoal font-medium">
                                No departures found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($departures->hasPages())
            <div class="px-6 py-4 border-t border-line">
                {{ $departures->links() }}
            </div>
        @endif
    </div>
@endsection
