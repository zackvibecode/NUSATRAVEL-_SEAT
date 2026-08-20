<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'Nusa Travel') — Trip Seat Availability</title>

        @include('partials.assets')
    </head>
    <body class="bg-fog text-ink antialiased">
        <div class="min-h-screen flex">
            <!-- Sidebar — Vercel clean, collapsible -->
            <aside id="appSidebar" class="w-64 bg-surface border-r border-line flex-shrink-0 hidden md:flex flex-col sticky top-0 h-screen overflow-x-hidden">
                <div class="sidebar-header px-5 py-5 border-b border-line flex items-center justify-between gap-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 min-w-0" title="Nusa Travel">
                        @include('partials.brand-mark', ['size' => 'w-9 h-9'])
                        <div class="sidebar-label leading-none min-w-0">
                            <span class="font-black tracking-tight whitespace-nowrap block">nusa<span class="text-brand">travel</span></span>
                            <span class="text-[9px] font-bold tracking-[0.2em] text-charcoal/70 block mt-0.5">TRIP SEAT</span>
                        </div>
                    </a>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        @include('partials.theme-toggle', ['compact' => true])
                        <button type="button" id="sidebarToggle" aria-label="Toggle sidebar" aria-expanded="true"
                                class="w-8 h-8 rounded-md text-charcoal hover:bg-fog hover:text-ink flex items-center justify-center transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <nav class="flex-1 px-3 py-3 space-y-0.5">
                    @php
                        $isAdmin = auth()->user()->isAdmin();
                        // Badge counts open upcoming trips that still need registrations
                        $attentionTripsCount = \App\Models\Departure::query()
                            ->withSum('registrations as registered_pax_sum', 'pax')
                            ->upcoming()
                            ->get()
                            ->filter(fn ($d) => $d->status_label === 'open')
                            ->count();
                        $navItems = [
                            [
                                'route' => 'dashboard',
                                'pattern' => 'dashboard',
                                'label' => 'Dashboard',
                                'short' => 'Home',
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"/></svg>',
                            ],
                            [
                                'route' => 'hermes.chat',
                                'pattern' => 'hermes.chat',
                                'label' => 'Hermes Chat',
                                'short' => 'Hermes',
                                'admin' => true,
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
                            ],
                            [
                                'route' => 'departures.index',
                                'pattern' => 'departures.*',
                                'label' => 'Trips / Departures',
                                'short' => 'Trips',
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                            ],
                            [
                                'route' => 'attention-trips.index',
                                'pattern' => 'attention-trips.*',
                                'label' => 'Attention Trips',
                                'short' => 'Attention',
                                'badge' => $attentionTripsCount,
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
                            ],
                            [
                                'route' => 'packages.index',
                                'pattern' => 'packages.*',
                                'label' => 'Packages',
                                'short' => 'Pkg',
                                'admin' => true,
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                            ],
                            [
                                'route' => 'need-partner.index',
                                'pattern' => 'need-partner.*',
                                'label' => 'Need Partner',
                                'short' => 'Partner',
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                            ],
                            [
                                'route' => 'reports.index',
                                'pattern' => 'reports.*',
                                'label' => 'Reports',
                                'short' => 'Report',
                                'admin' => true,
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
                            ],
                            [
                                'route' => 'calendar.index',
                                'pattern' => 'calendar.*',
                                'label' => 'Calendar',
                                'short' => 'Cal',
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                            ],
                            [
                                'route' => 'users.index',
                                'pattern' => 'users.*',
                                'label' => 'Users',
                                'short' => 'Users',
                                'admin' => true,
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 008 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H2a2 2 0 010-4h.09A1.65 1.65 0 004.6 8a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V2a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H22a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>',
                            ],
                            [
                                'route' => 'hermes.updates',
                                'pattern' => 'hermes.updates',
                                'label' => 'Hermes Update',
                                'short' => 'Update',
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>',
                            ],
                        ];

                        $navItems = array_filter($navItems, fn ($item) => ! ($item['admin'] ?? false) || $isAdmin);
                    @endphp

                    @foreach ($navItems as $item)
                        @php
                            $isActive = request()->routeIs($item['pattern']);
                        @endphp
                        <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors {{ $isActive ? 'bg-fog text-ink font-semibold' : 'text-charcoal hover:bg-fog hover:text-ink' }}">
                            <span class="{{ $isActive ? 'text-ink' : 'text-charcoal' }} flex-shrink-0">{!! $item['icon'] !!}</span>
                            <span class="sidebar-label whitespace-nowrap flex-1">{{ $item['label'] }}</span>
                            @if (($item['badge'] ?? 0) > 0)
                                <span class="sidebar-badge inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-brand text-white text-[10px] font-black flex-shrink-0">
                                    {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <div class="sidebar-footer px-5 py-5 border-t border-line text-sm">
                    <div class="sidebar-user flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-brand-soft text-brand font-bold text-sm flex-shrink-0" title="{{ auth()->user()->name }}">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <div class="sidebar-user-info min-w-0">
                            <div class="text-ink font-medium truncate">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-charcoal truncate">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" title="Logout"
                                class="w-full flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-charcoal hover:bg-fog hover:text-ink transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="sidebar-label whitespace-nowrap">Logout</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Mobile top bar -->
            <div class="md:hidden fixed top-0 inset-x-0 z-20 bg-surface border-b border-line px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    @include('partials.brand-mark', ['size' => 'w-7 h-7'])
                    <span class="font-black tracking-tight text-sm">nusa<span class="text-brand">travel</span></span>
                </a>
                <div class="flex items-center gap-2">
                    @include('partials.theme-toggle', ['compact' => true])
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-ink text-sm font-medium">Logout</button>
                    </form>
                </div>
            </div>

            <!-- Mobile bottom nav: 4 primary items + More drawer -->
            @php
                $primaryMobile = collect($navItems)->take(4)->all();
                $secondaryMobile = collect($navItems)->slice(4)->values()->all();
                $secondaryActive = collect($secondaryMobile)->contains(fn ($item) => request()->routeIs($item['pattern']));
            @endphp
            <nav class="md:hidden fixed bottom-0 inset-x-0 z-20 bg-surface border-t border-line flex items-center justify-around py-2 px-1 pb-[max(0.5rem,env(safe-area-inset-bottom))]">
                @foreach ($primaryMobile as $item)
                    @php
                        $isActive = request()->routeIs($item['pattern']);
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex flex-col items-center gap-1 px-3 py-1.5 rounded-xl text-[10px] font-semibold transition-colors {{ $isActive ? 'text-brand' : 'text-charcoal' }}">
                        <span class="relative {{ $isActive ? 'text-brand' : 'text-charcoal' }}">
                            {!! $item['icon'] !!}
                            @if (($item['badge'] ?? 0) > 0)
                                <span class="absolute -top-1.5 -right-2 inline-flex items-center justify-center min-w-[1rem] h-4 px-1 rounded-full bg-brand text-white text-[9px] font-black">
                                    {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                </span>
                            @endif
                        </span>
                        <span>{{ $item['short'] ?? $item['label'] }}</span>
                    </a>
                @endforeach
                <button type="button" id="moreNavBtn" aria-haspopup="true" aria-expanded="false" aria-controls="moreNavDrawer"
                        class="flex flex-col items-center gap-1 px-3 py-1.5 rounded-xl text-[10px] font-semibold transition-colors {{ $secondaryActive ? 'text-brand' : 'text-charcoal' }}">
                    <svg class="w-5 h-5 {{ $secondaryActive ? 'text-brand' : 'text-charcoal' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span>More</span>
                </button>
            </nav>

            <!-- Mobile More drawer -->
            <div id="moreNavDrawer" class="md:hidden fixed inset-0 z-30 hidden" role="dialog" aria-modal="true" aria-label="More navigation">
                <div id="moreNavBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
                <div class="absolute bottom-0 inset-x-0 bg-surface rounded-t-3xl border-t border-line max-h-[75vh] overflow-y-auto pb-[max(1rem,env(safe-area-inset-bottom))]">
                    <div class="sticky top-0 bg-surface px-5 pt-4 pb-3 border-b border-line flex items-center justify-between">
                        <h3 class="font-black tracking-tight">More</h3>
                        <button type="button" id="moreNavClose" aria-label="Close menu" class="w-9 h-9 rounded-full bg-fog hover:bg-brand-soft text-charcoal hover:text-brand flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-3 grid grid-cols-2 gap-2">
                        @foreach ($secondaryMobile as $item)
                            @php
                                $isActive = request()->routeIs($item['pattern']);
                            @endphp
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-colors {{ $isActive ? 'bg-brand text-white' : 'bg-fog text-ink hover:bg-brand-soft hover:text-brand' }}">
                                <span class="{{ $isActive ? 'text-white' : 'text-charcoal' }}">{!! $item['icon'] !!}</span>
                                <span class="flex-1">{{ $item['label'] }}</span>
                                @if (($item['badge'] ?? 0) > 0)
                                    <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full {{ $isActive ? 'bg-surface text-brand' : 'bg-brand text-white' }} text-[10px] font-black flex-shrink-0">
                                        {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('mobile-logout-form').submit();"
                           class="col-span-2 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold bg-fog text-ink hover:bg-red-50 hover:text-red-600 transition-colors">
                            <svg class="w-5 h-5 text-charcoal" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <main class="flex-1 md:ml-0 px-4 md:px-8 py-6 md:py-8 pt-16 md:pt-8 pb-24 md:pb-8">
                <div class="max-w-6xl mx-auto">
                    @if (session('success'))
                        <div class="mb-4 bg-positive-soft border border-positive/20 text-positive text-sm font-semibold rounded-xl px-5 py-3.5 flex items-center gap-3">
                            <svg class="w-5 h-5 text-positive flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm font-semibold rounded-xl px-5 py-3.5 flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm font-semibold rounded-xl px-5 py-3.5">
                            <div class="flex items-center gap-3 mb-1">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Please fix the following:</span>
                            </div>
                            <ul class="list-disc pl-9 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
            <!-- Hidden logout form for mobile drawer -->
            <form method="POST" action="{{ route('logout') }}" id="mobile-logout-form" class="hidden">
                @csrf
            </form>

            <script>
                (function () {
                    // Sidebar collapse — persist across pages
                    var toggle = document.getElementById('sidebarToggle');
                    if (localStorage.getItem('sidebar-collapsed') === '1') {
                        document.documentElement.classList.add('sidebar-collapsed');
                        toggle?.setAttribute('aria-expanded', 'false');
                    }

                    toggle?.addEventListener('click', function () {
                        var collapsed = document.documentElement.classList.toggle('sidebar-collapsed');
                        localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0');
                        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                    });

                    // Mobile More drawer
                    var drawer = document.getElementById('moreNavDrawer');
                    var btn = document.getElementById('moreNavBtn');
                    var closeBtn = document.getElementById('moreNavClose');
                    var backdrop = document.getElementById('moreNavBackdrop');

                    function openDrawer() {
                        drawer.classList.remove('hidden');
                        btn.setAttribute('aria-expanded', 'true');
                        document.body.style.overflow = 'hidden';
                        closeBtn.focus();
                    }

                    function closeDrawer() {
                        drawer.classList.add('hidden');
                        btn.setAttribute('aria-expanded', 'false');
                        document.body.style.overflow = '';
                        btn.focus();
                    }

                    btn?.addEventListener('click', openDrawer);
                    closeBtn?.addEventListener('click', closeDrawer);
                    backdrop?.addEventListener('click', closeDrawer);
                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && !drawer.classList.contains('hidden')) closeDrawer();
                    });
                })();
            </script>
        </div>

        @yield('scripts')

        {{-- Global delete-trip confirm modal (admin only, used by trip cards) --}}
        @if (auth()->user()->isAdmin())
            <div id="deleteTripModal"
                 class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-ink/40 backdrop-blur-[2px]"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="deleteTripTitle">
                <div class="bg-surface rounded-3xl shadow-xl border border-line w-full max-w-md p-6 sm:p-8">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 id="deleteTripTitle" class="text-lg font-semibold tracking-tight text-ink">Padam trip ini?</h3>
                            <p class="text-sm text-charcoal mt-2 leading-relaxed">
                                Trip <span id="deleteTripLabel" class="font-bold text-ink"></span> akan
                                <strong>hilang dari database</strong>.
                            </p>
                            <p id="deleteTripMeta" class="text-xs text-charcoal/80 mt-2"></p>
                        </div>
                    </div>

                    <form id="deleteTripForm" method="POST" class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                id="deleteTripCancel"
                                class="inline-flex justify-center items-center rounded-full px-5 py-2.5 text-sm font-semibold text-charcoal border border-line hover:bg-fog transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="inline-flex justify-center items-center rounded-full px-5 py-2.5 text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-colors">
                            Ya, padam
                        </button>
                    </form>
                </div>
            </div>

            <script>
                (function () {
                    const modal = document.getElementById('deleteTripModal');
                    const form = document.getElementById('deleteTripForm');
                    const labelEl = document.getElementById('deleteTripLabel');
                    const metaEl = document.getElementById('deleteTripMeta');
                    const cancelBtn = document.getElementById('deleteTripCancel');

                    if (!modal || !form) return;

                    function openModal(url, label, registrations) {
                        form.action = url;
                        labelEl.textContent = label;
                        const count = Number(registrations) || 0;
                        metaEl.textContent = count > 0
                            ? `Trip ini ada ${count} pax berdaftar — semua akan dipadam sekali.`
                            : 'Trip ini tiada pax berdaftar.';
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    }

                    function closeModal() {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        form.action = '';
                    }

                    document.addEventListener('click', function (e) {
                        const btn = e.target.closest('[data-delete-trip]');
                        if (btn) {
                            e.preventDefault();
                            openModal(
                                btn.getAttribute('data-delete-url'),
                                btn.getAttribute('data-trip-label') || 'trip ini',
                                btn.getAttribute('data-trip-registrations')
                            );
                        }
                    });

                    cancelBtn?.addEventListener('click', closeModal);
                    modal.addEventListener('click', (e) => {
                        if (e.target === modal) closeModal();
                    });
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
                    });
                })();
            </script>
        @endif
    </body>
</html>
