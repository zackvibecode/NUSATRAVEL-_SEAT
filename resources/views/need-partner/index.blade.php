@extends('layouts.app')

@section('title', 'Need Partner')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight leading-none">Need Partner</h2>
            <p class="text-sm text-charcoal mt-2">Solo travellers who need a room-sharing partner (PRD 13).</p>
        </div>
    </div>

    <!-- Summary counts -->
    <div class="grid grid-cols-2 gap-4 mb-6 max-w-md">
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Need Male Partner</p>
            </div>
            <p class="text-4xl font-black leading-none">{{ $maleCount }}</p>
        </div>
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Need Female Partner</p>
            </div>
            <p class="text-4xl font-black leading-none">{{ $femaleCount }}</p>
        </div>
    </div>

    <!-- Auto-match suggestions -->
    @if ($matches->isNotEmpty())
        <div class="bg-white rounded-3xl shadow-sm border border-line p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-positive-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-positive" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg tracking-tight">Suggested Room Matches</h3>
                    <p class="text-xs text-charcoal mt-0.5 font-medium">Auto-paired solo travellers on the same trip with same gender</p>
                </div>
            </div>
            <div class="space-y-3">
                @foreach ($matches as $match)
                    <div class="flex items-center justify-between bg-fog rounded-2xl px-5 py-4">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center -space-x-2">
                                <span class="w-8 h-8 rounded-full bg-brand-soft text-brand font-bold text-xs flex items-center justify-center border-2 border-white">
                                    {{ strtoupper(substr($match['pair'][0]->name, 0, 1)) }}
                                </span>
                                <span class="w-8 h-8 rounded-full bg-brand-soft text-brand font-bold text-xs flex items-center justify-center border-2 border-white">
                                    {{ strtoupper(substr($match['pair'][1]->name, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <p class="font-bold text-sm">{{ $match['pair'][0]->name }} + {{ $match['pair'][1]->name }}</p>
                                <p class="text-xs text-charcoal mt-0.5">
                                    {{ $match['departure']->package->name }} · {{ $match['departure']->departure_date->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-positive-soft text-positive">
                            {{ ucfirst($match['type']) }} pair
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-line overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-charcoal border-b border-line bg-fog/50">
                        <th class="px-6 py-4 font-semibold">Name</th>
                        <th class="px-6 py-4 font-semibold">Package</th>
                        <th class="px-6 py-4 font-semibold">Departure</th>
                        <th class="px-6 py-4 font-semibold">Partner Needed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($registrations as $registration)
                        <tr class="transition-colors hover:bg-fog/50">
                            <td class="px-6 py-4 font-bold">{{ $registration->name }}</td>
                            <td class="px-6 py-4 text-charcoal font-medium">{{ $registration->departure->package->name }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('departures.show', $registration->departure) }}"
                                   class="text-brand hover:text-brand-hover font-bold">
                                    {{ $registration->departure->departure_date->format('d M Y') }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-warning-soft text-warning">
                                    {{ $registration->partner_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-charcoal font-medium">
                                No registrations need a partner right now.
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
