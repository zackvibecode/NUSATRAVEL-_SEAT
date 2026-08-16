@extends('layouts.app')

@section('title', 'Create Package')

@section('content')
    <div class="max-w-xl mx-auto">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-none">Create Package</h2>
                <p class="text-sm text-charcoal mt-2">A package is the reusable travel product (e.g. Yunnan 8D6N).</p>
            </div>
            <a href="{{ route('packages.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-charcoal hover:text-ink">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>

        <form method="POST" action="{{ route('packages.store') }}" class="bg-white rounded-xl shadow-sm border border-line p-4 sm:p-6 space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold text-ink mb-2">Package Name *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    placeholder="e.g. Yunnan 8D6N"
                    class="w-full rounded-xl border @error('name') border-red-400 @else border-line @enderror bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
                @include('partials.field-error', ['field' => 'name'])
            </div>

            <div>
                <label for="destination" class="block text-sm font-semibold text-ink mb-2">Destination *</label>
                <input
                    type="text"
                    id="destination"
                    name="destination"
                    value="{{ old('destination') }}"
                    required
                    placeholder="e.g. China"
                    class="w-full rounded-xl border @error('destination') border-red-400 @else border-line @enderror bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
                @include('partials.field-error', ['field' => 'destination'])
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-ink mb-2">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Optional. e.g. Kunming, Dali, Lijiang, Shangri-La"
                    class="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="status" class="block text-sm font-semibold text-ink mb-2">Status</label>
                <select
                    id="status"
                    name="status"
                    class="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="archived" @selected(old('status') === 'archived')>Archived</option>
                </select>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit"
                        class="bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
                    Save Package
                </button>
                <a href="{{ route('packages.index') }}" class="text-sm font-bold text-charcoal hover:text-ink">Cancel</a>
            </div>
        </form>
    </div>
@endsection
