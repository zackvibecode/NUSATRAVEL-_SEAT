@extends('layouts.app')

@section('title', 'Create Departure')

@section('content')
    <div class="max-w-xl mx-auto">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">Create Departure</h2>
                <p class="text-sm text-charcoal mt-2">One specific travel date under a package.</p>
            </div>
            <a href="{{ route('departures.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-charcoal hover:text-ink">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>

        <form method="POST" action="{{ route('departures.store') }}" class="bg-white rounded-lg border border-line p-4 sm:p-6 space-y-6">
            @csrf

            <div>
                <label for="package_id" class="block text-sm font-semibold text-ink mb-2">Package *</label>
                <select
                    id="package_id"
                    name="package_id"
                    required
                    class="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
                    <option value="">Select an active package...</option>
                    @foreach ($packages as $package)
                        <option value="{{ $package->id }}" @selected(old('package_id') == $package->id)>{{ $package->name }}</option>
                    @endforeach
                </select>
                @include('partials.field-error', ['field' => 'package_id'])
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="departure_date" class="block text-sm font-semibold text-ink mb-2">Departure Date *</label>
                    <input
                        type="date"
                        id="departure_date"
                        name="departure_date"
                        value="{{ old('departure_date') }}"
                        required
                        class="w-full rounded-xl border @error('departure_date') border-red-400 @else border-line @enderror bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                    >
                    @include('partials.field-error', ['field' => 'departure_date'])
                </div>
                <div>
                    <label for="return_date" class="block text-sm font-semibold text-ink mb-2">Return Date *</label>
                    <input
                        type="date"
                        id="return_date"
                        name="return_date"
                        value="{{ old('return_date') }}"
                        required
                        class="w-full rounded-xl border @error('return_date') border-red-400 @else border-line @enderror bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                    >
                    @include('partials.field-error', ['field' => 'return_date'])
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="total_seats" class="block text-sm font-semibold text-ink mb-2">Total Seats *</label>
                    <input
                        type="number"
                        id="total_seats"
                        name="total_seats"
                        value="{{ old('total_seats', 25) }}"
                        min="1"
                        required
                        class="w-full rounded-xl border @error('total_seats') border-red-400 @else border-line @enderror bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                    >
                    @include('partials.field-error', ['field' => 'total_seats'])
                </div>
            </div>

            <div>
                <label for="airline" class="block text-sm font-semibold text-ink mb-2">Airline</label>
                <input
                    type="text"
                    id="airline"
                    name="airline"
                    value="{{ old('airline') }}"
                    placeholder="e.g. AirAsia"
                    class="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
            </div>

            <div>
                <label for="notes" class="block text-sm font-semibold text-ink mb-2">Notes</label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    class="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit"
                        class="bg-ink hover:bg-black text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-colors duration-150">
                    Save Departure
                </button>
                <a href="{{ route('departures.index') }}" class="text-sm font-bold text-charcoal hover:text-ink">Cancel</a>
            </div>
        </form>
    </div>
@endsection
