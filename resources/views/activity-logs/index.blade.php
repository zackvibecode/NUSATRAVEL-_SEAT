@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">Activity Log</h2>
            <p class="text-sm text-charcoal mt-2">Who changed what, and when — every manual add, edit, and delete.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg border border-line p-4 sm:p-5 mb-4">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[240px]">
                <label for="search" class="block text-[11px] font-semibold text-charcoal mb-1.5">Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ $searchFilter }}"
                    placeholder="Search user or record..."
                    class="w-full rounded-xl border border-line bg-white px-4 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
            </div>
            <div>
                <label for="action" class="block text-[11px] font-semibold text-charcoal mb-1.5">Action</label>
                <select name="action" id="action" class="rounded-xl border border-line bg-white px-3 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all min-w-[8rem]">
                    <option value="" @selected($actionFilter === '')>All Actions</option>
                    <option value="created" @selected($actionFilter === 'created')>Created</option>
                    <option value="updated" @selected($actionFilter === 'updated')>Updated</option>
                    <option value="deleted" @selected($actionFilter === 'deleted')>Deleted</option>
                </select>
            </div>
            <div>
                <label for="subject" class="block text-[11px] font-semibold text-charcoal mb-1.5">Record Type</label>
                <select name="subject" id="subject" class="rounded-xl border border-line bg-white px-3 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all min-w-[9rem]">
                    <option value="" @selected($subjectFilter === '')>All Types</option>
                    <option value="registration" @selected($subjectFilter === 'registration')>Registration</option>
                    <option value="departure" @selected($subjectFilter === 'departure')>Departure</option>
                    <option value="package" @selected($subjectFilter === 'package')>Package</option>
                    <option value="user" @selected($subjectFilter === 'user')>User</option>
                </select>
            </div>
            <button type="submit"
                    class="bg-brand hover:bg-brand-hover text-white text-xs font-bold rounded-full px-5 py-2.5 transition-all duration-150 hover:scale-[1.03] shadow-sm">
                Filter
            </button>
            <a href="{{ route('activity-logs.index') }}" class="text-xs font-semibold text-charcoal hover:text-ink py-2.5">Reset</a>
        </form>
    </div>

    <!-- Log table -->
    <div class="bg-white rounded-lg border border-line overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="text-left text-charcoal border-b border-line bg-fog/50">
                        <th class="px-5 py-4 font-semibold">When</th>
                        <th class="px-5 py-4 font-semibold">User</th>
                        <th class="px-5 py-4 font-semibold">Action</th>
                        <th class="px-5 py-4 font-semibold">Record</th>
                        <th class="px-5 py-4 font-semibold">Changes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($logs as $log)
                        <tr class="transition-colors hover:bg-fog/50">
                            <td class="px-5 py-4 text-charcoal font-medium whitespace-nowrap">
                                {{ $log->created_at?->timezone(auth()->user()->timezone ?? config('app.timezone'))->format('d M Y, H:i') }}
                            </td>
                            <td class="px-5 py-4 font-bold">{{ $log->user_name }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold {{ $log->action_color }}">
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold">{{ $log->subject_label }}</p>
                                <p class="text-xs text-charcoal/70 mt-0.5">{{ ucfirst($log->subject_type) }}{{ $log->subject_id ? ' #'.$log->subject_id : '' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @if ($log->changes)
                                    <div class="space-y-1">
                                        @foreach ($log->changes as $field => $change)
                                            @php
                                                $old = trim((string) ($change['old'] ?? '')) !== '' ? $change['old'] : '—';
                                                $new = trim((string) ($change['new'] ?? '')) !== '' ? $change['new'] : '—';
                                            @endphp
                                            <p class="text-xs text-charcoal">
                                                <span class="font-bold">{{ ucwords(str_replace('_', ' ', $field)) }}:</span>
                                                <span class="line-through opacity-60">{{ $old }}</span>
                                                <span class="mx-1">→</span>
                                                <span class="font-semibold">{{ $new }}</span>
                                            </p>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-charcoal/60 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-charcoal font-medium">
                                No activity recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($logs->hasPages())
        <div class="bg-white rounded-lg border border-line px-6 py-4 mt-4">
            {{ $logs->links() }}
        </div>
    @endif
@endsection
