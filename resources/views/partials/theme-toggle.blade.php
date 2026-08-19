@php
    $compact = $compact ?? false;
@endphp

<button type="button"
        data-theme-toggle
        aria-label="Toggle dark mode"
        title="Toggle light / dark"
        class="{{ $compact ? 'w-9 h-9' : 'w-full' }} flex items-center {{ $compact ? 'justify-center' : 'gap-2 px-3 py-2' }} rounded-md text-sm font-medium text-charcoal hover:bg-fog hover:text-ink transition-colors">
    <svg class="theme-icon-moon w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
    </svg>
    <svg class="theme-icon-sun w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364l-1.414-1.414M7.05 7.05L5.636 5.636m12.728 0L17.95 7.05M7.05 16.95l-1.414 1.414M12 8a4 4 0 100 8 4 4 0 000-8z"/>
    </svg>
    @unless ($compact)
        <span class="sidebar-label whitespace-nowrap">
            <span class="theme-icon-moon">Dark mode</span>
            <span class="theme-icon-sun">Light mode</span>
        </span>
    @endunless
</button>
