<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Login — Nusa Travel</title>

        @include('partials.assets')
    </head>
    <body class="bg-surface text-ink antialiased min-h-screen">
        <div class="min-h-screen flex flex-col lg:flex-row">
            <!-- Left panel — brand showcase with airplane window view -->
            <div class="lg:w-1/2 relative overflow-hidden flex flex-col justify-between p-8 lg:p-12 text-white">
                <!-- Background: airplane wing above clouds -->
                <div class="absolute inset-0">
                    <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?q=80&w=1920&auto=format&fit=crop"
                         alt="" aria-hidden="true"
                         class="w-full h-full object-cover">
                    <!-- Strong dark overlay so white text always reads clearly -->
                    <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(196,0,39,0.88) 0%, rgba(120,0,24,0.82) 50%, rgba(30,0,7,0.94) 100%);"></div>
                </div>

                <div class="relative z-10">
                    <a href="#" class="flex items-center gap-3">
                        @include('partials.brand-mark', ['size' => 'w-12 h-12'])
                        <div class="leading-none">
                            <span class="text-2xl font-black tracking-tight block">nusa<span class="text-white">travel</span></span>
                            <span class="text-[11px] font-bold tracking-[0.25em] text-white/70 block mt-1.5">TRIP SEAT</span>
                        </div>
                    </a>
                </div>

                <div class="relative z-10 max-w-md">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[0.95]">
                        Fly Full.<br>Never Overbook.
                    </h1>
                    <p class="text-white/90 text-base sm:text-lg mt-5 font-medium leading-relaxed">
                        Track seats, pax, and payments for every departure — in one clean dashboard.
                    </p>

                    <div class="mt-8 space-y-3">
                        <div class="flex items-center gap-4 bg-surface/15 backdrop-blur rounded-2xl px-4 py-3 border border-white/20">
                            <svg class="w-5 h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <p class="font-bold text-sm">Real-time seat tracking</p>
                                <p class="text-white/80 text-xs mt-0.5">Capacity & occupancy at a glance</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 bg-surface/15 backdrop-blur rounded-2xl px-4 py-3 border border-white/20">
                            <svg class="w-5 h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <div>
                                <p class="font-bold text-sm">Payment alerts</p>
                                <p class="text-white/80 text-xs mt-0.5">Chase unpaid & partial invoices</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 bg-surface/15 backdrop-blur rounded-2xl px-4 py-3 border border-white/20">
                            <svg class="w-5 h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <div>
                                <p class="font-bold text-sm">Monthly reports</p>
                                <p class="text-white/80 text-xs mt-0.5">One-click insights</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 text-white/70 text-sm font-medium">
                    Internal system. Authorized staff only.
                </div>
            </div>

            <!-- Right panel — login form -->
            <div class="lg:w-1/2 flex items-center justify-center p-6 lg:p-12 bg-fog">
                <div class="w-full max-w-md">
                    <div class="lg:hidden text-center mb-8">
                        @include('partials.brand-mark', ['size' => 'w-14 h-14 mx-auto'])
                        <h1 class="text-3xl font-black tracking-tight mt-4">nusa<span class="text-brand">travel</span></h1>
                        <p class="text-xs font-bold tracking-[0.25em] text-charcoal mt-1.5">TRIP SEAT</p>
                    </div>

                    <div class="bg-surface rounded-3xl p-8 shadow-sm border border-line">
                        <div class="flex justify-end mb-4 lg:mb-0">
                        @include('partials.theme-toggle', ['compact' => true])
                    </div>
                    <div class="mb-8">
                            @include('partials.brand-mark', ['size' => 'w-14 h-14 mb-6 hidden lg:inline-flex'])
                            <h2 class="text-2xl font-black tracking-tight">Welcome back</h2>
                            <p class="text-charcoal text-sm mt-1.5">Enter your credentials to access the dashboard.</p>
                        </div>

                        @if (session('status'))
                            <div class="mb-5 bg-positive-soft border border-positive/20 text-positive text-sm font-semibold rounded-2xl px-5 py-3.5">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-5 bg-brand-soft border border-brand/20 text-brand-ink text-sm font-medium rounded-2xl px-5 py-3.5">
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                            @csrf

                            <div>
                                <label for="email" class="block text-sm font-semibold text-ink mb-2">Email address</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    autocomplete="email"
                                    placeholder="you@company.com"
                                    class="w-full rounded-2xl border @error('email') border-red-400 @else border-line @enderror bg-surface px-4 py-3.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                                >
                                @include('partials.field-error', ['field' => 'email'])
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-semibold text-ink mb-2">Password</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full rounded-2xl border @error('password') border-red-400 @else border-line @enderror bg-surface px-4 py-3.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                                >
                                @include('partials.field-error', ['field' => 'password'])
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2 text-sm text-charcoal font-medium">
                                    <svg class="w-4 h-4 text-positive" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Stay signed in
                                </span>
                                <a href="{{ route('password.request') }}" class="text-sm font-bold text-brand hover:text-brand-hover">
                                    Forgot password?
                                </a>
                            </div>

                            <button
                                type="submit"
                                class="w-full bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-4 transition-all duration-150 hover:scale-[1.02] shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                            >
                                <span>Log in</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        </form>

                        <div class="flex items-center gap-4 my-6" role="separator" aria-label="or">
                            <span class="h-px flex-1 bg-line"></span>
                            <span class="text-xs font-semibold uppercase tracking-widest text-charcoal">or</span>
                            <span class="h-px flex-1 bg-line"></span>
                        </div>

                        <a href="{{ route('google.login') }}"
                           class="w-full bg-surface hover:bg-fog border border-line hover:border-charcoal/30 text-ink text-sm font-bold rounded-full px-6 py-4 transition-all duration-150 hover:scale-[1.02] shadow-sm flex items-center justify-center gap-3">
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span>Continue with Google</span>
                        </a>
                    </div>

                    <p class="text-center text-xs text-charcoal mt-6 font-medium">
                        Internal system. Authorized staff only.
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
