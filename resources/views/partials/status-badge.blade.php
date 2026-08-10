@php
    $styles = [
        'open' => 'bg-positive-soft text-positive',
        'almost_full' => 'bg-warning-soft text-warning',
        'full' => 'bg-brand text-white',
        'departed' => 'bg-gray-200 text-gray-700',
        'cancelled' => 'bg-gray-100 text-gray-500 line-through',
        'active' => 'bg-positive-soft text-positive',
        'archived' => 'bg-gray-100 text-gray-500',
    ];
    $labels = [
        'open' => 'Open',
        'almost_full' => 'Almost Full',
        'full' => 'Full',
        'departed' => 'Departed',
        'cancelled' => 'Cancelled',
        'active' => 'Active',
        'archived' => 'Archived',
    ];
@endphp

<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $styles[$status] ?? 'bg-gray-100 text-gray-600' }}">
    {{ $labels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
</span>
