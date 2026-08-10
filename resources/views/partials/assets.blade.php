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
                        brand: { DEFAULT: '#e4002b', hover: '#c40027', soft: '#ffe9ed', ink: '#3d000a' },
                        fog: '#faf3f3',
                        ink: '#14100f',
                        charcoal: '#454245',
                        line: '#e8e4e2',
                    }
                }
            }
        }
    </script>
@endif

@if ($jsFile)
    <script type="module" src="{{ asset('build/'.$jsFile) }}"></script>
@endif
