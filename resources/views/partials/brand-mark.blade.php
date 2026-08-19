@php
    /** @var string $size Tailwind size utility (e.g. 'w-9 h-9'). */
    $size = $size ?? 'w-9 h-9';
    /** @var string $extraClass Optional extra classes on the outer wrapper. */
    $extraClass = $extraClass ?? '';
@endphp

<span class="inline-flex {{ $extraClass }} {{ $size }} flex-shrink-0 overflow-hidden rounded-full bg-black select-none">
    <img
        src="{{ asset('logo-nusa.png') }}"
        alt="Nusa Travel"
        class="w-full h-full object-cover object-center scale-[1.28]"
        width="120"
        height="120"
        decoding="async"
    >
</span>
