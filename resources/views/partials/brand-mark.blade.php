@php
    /** @var string $size Tailwind size utility (e.g. 'w-9 h-9') — controls rendered size. */
    $size = $size ?? 'w-9 h-9';
    /** @var string|null $extraClass Optional extra classes appended to the outer span. */
    $extraClass = $extraClass ?? '';
    /** @var string $gradientId Unique gradient id to avoid SVG id collisions when included multiple times on a page. */
    $gradientId = 'logo-grad-'.uniqid();
@endphp

<span class="inline-flex items-center {{ $extraClass }} {{ $size }} flex-shrink-0 select-none">
    <svg class="w-full h-full" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" aria-label="Nusa Travel logo" role="img">
        <defs>
            <linearGradient id="{{ $gradientId }}" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#ff1a3c"/>
                <stop offset="100%" stop-color="#c40027"/>
            </linearGradient>
        </defs>
        <circle cx="60" cy="60" r="60" fill="url(#{{ $gradientId }})"/>
        <text x="60" y="48"
              text-anchor="middle"
              dominant-baseline="central"
              font-family="'Geist', system-ui, -apple-system, sans-serif"
              font-weight="900"
              font-size="22"
              letter-spacing="-0.5"
              fill="#ffffff"
              font-style="italic">nusa</text>
        <text x="60" y="74"
              text-anchor="middle"
              dominant-baseline="central"
              font-family="'Geist', system-ui, -apple-system, sans-serif"
              font-weight="900"
              font-size="22"
              letter-spacing="-0.5"
              fill="#ffffff"
              font-style="italic">travel</text>
    </svg>
</span>