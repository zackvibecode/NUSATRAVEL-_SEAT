<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'SeatWeb') — Trip Seat Availability</title>

        @include('partials.assets')
    </head>
    <body class="bg-fog text-ink antialiased">
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside class="w-64 bg-white border-r border-line flex-shrink-0 hidden md:flex flex-col sticky top-0 h-screen">
                <div class="px-6 py-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-brand text-white font-black text-lg shadow-sm">S</span>
                        <div>
                            <span class="text-lg font-black tracking-tight leading-none block">SeatWeb</span>
                            <span class="text-[11px] text-charcoal font-medium mt-0.5 block">Trip Seat Availability</span>
                        </div>
                    </a>
                </div>

                <nav class="flex-1 px-4 py-2 space-y-1">
                    @php
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
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
                            ],
                            [
                                'route' => 'hermes.updates',
                                'pattern' => 'hermes.updates',
                                'label' => 'Hermes Update',
                                'short' => 'Update',
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>',
                            ],
                            [
                                'route' => 'departures.index',
                                'pattern' => 'departures.*',
                                'label' => 'Trips / Departures',
                                'short' => 'Trips',
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                            ],
                            [
                                'route' => 'packages.index',
                                'pattern' => 'packages.*',
                                'label' => 'Packages',
                                'short' => 'Pkg',
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                            ],
                            [
                                'route' => 'participants.index',
                                'pattern' => 'participants.*',
                                'label' => 'Participants',
                                'short' => 'Pax',
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
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
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
                            ],
                            [
                                'route' => 'calendar.index',
                                'pattern' => 'calendar.*',
                                'label' => 'Calendar',
                                'short' => 'Cal',
                                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                            ],
                        ];
                    @endphp

                    @foreach ($navItems as $item)
                        @php
                            $isActive = request()->routeIs($item['pattern']);
                        @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-150 {{ $isActive ? 'bg-brand text-white shadow-md' : 'text-ink hover:bg-brand-soft hover:text-brand' }}">
                            <span class="{{ $isActive ? 'text-white' : 'text-charcoal' }}">{!! $item['icon'] !!}</span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="px-5 py-5 border-t border-line text-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-brand-soft text-brand font-bold text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <div class="min-w-0">
                            <div class="text-ink font-semibold truncate">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-charcoal truncate">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-ink hover:bg-brand-soft hover:text-brand transition-all duration-150">
                            <svg class="w-5 h-5 text-charcoal" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Mobile top bar -->
            <div class="md:hidden fixed top-0 inset-x-0 z-20 bg-white border-b border-line px-4 py-3 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-brand text-white font-black text-sm">S</span>
                    <span class="font-black tracking-tight">SeatWeb</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-brand text-sm font-semibold">Logout</button>
                </form>
            </div>

            <!-- Mobile bottom nav: 4 primary items + More drawer -->
            @php
                $primaryMobile = collect($navItems)->take(4)->all();
                $secondaryMobile = collect($navItems)->slice(4)->values()->all();
                $secondaryActive = collect($secondaryMobile)->contains(fn ($item) => request()->routeIs($item['pattern']));
            @endphp
            <nav class="md:hidden fixed bottom-0 inset-x-0 z-20 bg-white border-t border-line flex items-center justify-around py-2 px-1 pb-[max(0.5rem,env(safe-area-inset-bottom))]">
                @foreach ($primaryMobile as $item)
                    @php
                        $isActive = request()->routeIs($item['pattern']);
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex flex-col items-center gap-1 px-3 py-1.5 rounded-xl text-[10px] font-semibold transition-colors {{ $isActive ? 'text-brand' : 'text-charcoal' }}">
                        <span class="{{ $isActive ? 'text-brand' : 'text-charcoal' }}">{!! $item['icon'] !!}</span>
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
                <div class="absolute bottom-0 inset-x-0 bg-white rounded-t-3xl border-t border-line max-h-[75vh] overflow-y-auto pb-[max(1rem,env(safe-area-inset-bottom))]">
                    <div class="sticky top-0 bg-white px-5 pt-4 pb-3 border-b border-line flex items-center justify-between">
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
                                <span>{{ $item['label'] }}</span>
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
    </body>
</html>
