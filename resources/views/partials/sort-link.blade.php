@php
    /** @var \App\Support\TripListFilter $filter */
@endphp
<a href="{{ $filter->sortUrl($column, null, $extra ?? []) }}"
   class="inline-flex items-center gap-1 hover:text-brand font-semibold {{ ($filter->sort === $column) ? 'text-brand' : '' }}">
    {{ $label }}{{ $filter->sortIndicator($column) }}
</a>
