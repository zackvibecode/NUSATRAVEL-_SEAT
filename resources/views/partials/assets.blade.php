@include('partials.theme-boot')

<style>
    :root {
        --app-fog: #fafafa;
        --app-surface: #ffffff;
        --app-ink: #171717;
        --app-charcoal: #666666;
        --app-line: #eaeaea;
        --app-brand: #e4002b;
        --app-brand-hover: #c40027;
        --app-brand-soft: #fff1f2;
        --app-brand-ink: #3d000a;
        color-scheme: light;
    }
    html.dark {
        --app-fog: #07111f;
        --app-surface: #0f1c33;
        --app-ink: #e8eef8;
        --app-charcoal: #9bb0cc;
        --app-line: #24385c;
        --app-brand: #ff4d6d;
        --app-brand-hover: #ff6b85;
        --app-brand-soft: #2a1630;
        --app-brand-ink: #ffd6de;
        color-scheme: dark;
    }
    html.dark .theme-icon-sun { display: none; }
    html:not(.dark) .theme-icon-moon { display: none; }
</style>

{{-- Favicon (circular SeatWeb "S" mark) --}}
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

{{-- Typography: Geist + Geist Mono (Vercel-style clean) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Geist+Mono:wght@400;500&display=swap">

{{-- Load built assets without @vite (avoids ViteManifestNotFoundException 500) --}}
@php
    $manifestPath = public_path('build/manifest.json');
    $cssFile = null;
    $jsFile = null;

    if (is_file($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
    }
@endphp

@if ($cssFile)
    <link rel="stylesheet" href="{{ asset('build/'.$cssFile) }}">
@else
    {{-- Emergency fallback so login never goes blank/500 --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: 'var(--app-brand)', hover: 'var(--app-brand-hover)', soft: 'var(--app-brand-soft)', ink: 'var(--app-brand-ink)' },
                        fog: 'var(--app-fog)',
                        surface: 'var(--app-surface)',
                        ink: 'var(--app-ink)',
                        charcoal: 'var(--app-charcoal)',
                        line: 'var(--app-line)',
                    }
                }
            }
        }
    </script>
@endif

@if ($jsFile)
    <script type="module" src="{{ asset('build/'.$jsFile) }}"></script>
@endif
