@extends('layouts.app')

@section('title', 'Packages')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight leading-none">Packages</h2>
            <p class="text-sm text-charcoal mt-2">Reusable travel products. One package can have many departure dates.</p>
        </div>
        <a href="{{ route('packages.create') }}"
           class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Package
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-line overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-charcoal border-b border-line bg-fog/50">
                        <th class="px-6 py-4 font-semibold">Package</th>
                        <th class="px-6 py-4 font-semibold">Destination</th>
                        <th class="px-6 py-4 font-semibold">Description</th>
                        <th class="px-6 py-4 font-semibold text-center">Departures</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($packages as $package)
                        <tr class="transition-colors hover:bg-fog/50">
                            <td class="px-6 py-4 font-bold">{{ $package->name }}</td>
                            <td class="px-6 py-4 text-charcoal font-medium">{{ $package->destination }}</td>
                            <td class="px-6 py-4 text-charcoal max-w-xs truncate">{{ $package->description }}</td>
                            <td class="px-6 py-4 text-center text-charcoal font-semibold">{{ $package->departures_count }}</td>
                            <td class="px-6 py-4">
                                @include('partials.status-badge', ['status' => $package->status])
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('packages.edit', $package) }}"
                                       class="inline-flex items-center gap-1.5 text-brand hover:text-brand-hover text-sm font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>

                                    @if ($package->status === 'active')
                                        <form method="POST" action="{{ route('packages.destroy', $package) }}"
                                              onsubmit="return confirm('Archive this package? Historical departures and registrations will be kept.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 text-charcoal hover:text-brand text-sm font-semibold">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                </svg>
                                                Archive
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-charcoal font-medium">
                                No packages yet. Create your first package.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
