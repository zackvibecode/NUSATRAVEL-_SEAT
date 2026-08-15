@extends('layouts.app')

@section('title', 'Hermes Update')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-none">Hermes Update</h2>
        <p class="text-sm text-charcoal mt-2">Seat changes Hermes made. Latest first · travel date and time updated.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-line overflow-hidden">
        <div class="divide-y divide-line">
            @forelse ($activities as $activity)
                <div class="px-4 sm:px-6 py-3 flex items-center justify-between gap-3 text-sm font-medium">
                    <p class="min-w-0 truncate">
                        @if ($activity->departure_id)
                            <a href="{{ route('departures.show', $activity->departure_id) }}" class="hover:text-brand">{{ $activity->package_name }}</a>
                        @else
                            {{ $activity->package_name }}
                        @endif
                        <span class="text-charcoal">|</span>
                        <span class="{{ $activity->seat_delta > 0 ? 'text-positive' : 'text-brand' }}">{{ $activity->seat_change_label }}</span>
                        <span class="text-charcoal">|</span>
                        {{ $activity->departure_date->format('j M Y') }}
                        <span class="text-charcoal">|</span>
                        <span class="text-charcoal font-normal">updated {{ $activity->updated_at_label }}</span>
                    </p>
                </div>
            @empty
                <p class="px-4 sm:px-6 py-12 text-center text-sm text-charcoal font-medium">No seat updates from Hermes yet.</p>
            @endforelse
        </div>
    </div>

    @if ($activities->hasPages())
        <div class="mt-4">
            {{ $activities->links() }}
        </div>
    @endif
@endsection
