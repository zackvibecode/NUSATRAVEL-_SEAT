<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Login — SeatWeb</title>

        @include('partials.assets')
        <style>
            .login-mesh {
                background:
                    radial-gradient(at 0% 0%, #ffe9ed 0%, transparent 55%),
                    radial-gradient(at 100% 0%, #e4002b 0%, transparent 45%),
                    radial-gradient(at 100% 100%, #c40027 0%, transparent 45%),
                    radial-gradient(at 0% 100%, #fff5f5 0%, transparent 50%),
                    #8f0020;
            }
            .login-glow {
                position: absolute;
                border-radius: 9999px;
                filter: blur(90px);
                opacity: 0.5;
                pointer-events: none;
            }
        </style>
    </head>
    <body class="login-mesh text-ink antialiased min-h-screen relative overflow-hidden">
        {{-- Floating glow orbs for depth --}}
        <div class="login-glow w-96 h-96 bg-white" style="top:-10%; left:-8%;"></div>
        <div class="login-glow w-80 h-80 bg-brand" style="bottom:-12%; right:-6%;"></div>
        <div class="login-glow w-64 h-64 bg-warning-soft" style="top:35%; right:20%; opacity:0.35;"></div>

        <div class="min-h-screen flex items-center justify-center p-5 sm:p-8 relative z-10">
            <div class="w-full max-w-5xl grid lg:grid-cols-[1.15fr_1fr] gap-6 lg:gap-8 items-stretch">

                {{-- Brand panel (desktop) --}}
                <div class="hidden lg:flex flex-col justify-between rounded-3xl p-10 relative overflow-hidden border border-white/25"
                     style="background: linear-gradient(145deg, rgba(255,255,255,0.14), rgba(255,255,255,0.05)); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-white text-brand font-black text-xl shadow-lg">S</span>
                            <div>
                                <span class="text-2xl font-black tracking-tight text-white">SeatWeb</span>
                                <span class="text-[11px] text-white/70 font-medium block">Trip Seat Availability</span>
                            </div>
                        </div>

                        <h1 class="font-serif text-white text-5xl xl:text-6xl font-bold leading-[1.05] tracking-tight mt-12">
                            Travel seat<br>
                            management,<br>
                            <span class="italic">beautifully clear.</span>
                        </h1>
                        <p class="text-white/75 text-base mt-6 font-medium max-w-md leading-relaxed">
                            Registered pax, available seats, payment tracking and monthly reports — one internal workspace for your tour operations.
                        </p>
                    </div>

                    <div class="space-y-3.5">
                        <div class="flex items-center gap-3.5 rounded-2xl px-4 py-3.5 border border-white/20"
                             style="background: rgba(255,255,255,0.1);">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:18px;height:18px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-white">Real-time seat tracking</p>
                                <p class="text-white/65 text-xs mt-0.5">Capacity and occupancy at a glance</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3.5 rounded-2xl px-4 py-3.5 border border-white/20"
                             style="background: rgba(255,255,255,0.1);">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                                <svg class="text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:18px;height:18px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-white">Partner matching</p>
                                <p class="text-white/65 text-xs mt-0.5">Room-sharing made simple</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3.5 rounded-2xl px-4 py-3.5 border border-white/20"
                             style="background: rgba(255,255,255,0.1);">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                                <svg class="text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:18px;height:18px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-white">Monthly reports</p>
                                <p class="text-white/65 text-xs mt-0.5">Insights and CSV export in one click</p>
                            </div>
                        </div>
                    </div>

                    <p class="text-white/55 text-xs font-medium mt-8">
                        Internal system. Authorized staff only.
                    </p>
                </div>

                {{-- Login card --}}
                <div class="glass-strong rounded-3xl p-8 sm:p-10 w-full max-w-md lg:max-w-none mx-auto relative">
                    <div class="lg:hidden flex flex-col items-center text-center mb-8">
                        <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-brand text-white font-black text-2xl shadow-lg">S</span>
                        <h1 class="font-serif text-3xl font-bold tracking-tight mt-4">Trip Seat Availability</h1>
                        <p class="text-charcoal text-sm mt-1">Internal system. Authorized staff only.</p>
                    </div>

                    <div class="hidden lg:block mb-8">
                        <h2 class="font-serif text-3xl font-bold tracking-tight">Welcome back</h2>
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
                                class="w-full rounded-2xl border @error('email') border-red-400 @else border-line @enderror bg-white/70 px-4 py-3.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
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
                                class="w-full rounded-2xl border @error('password') border-red-400 @else border-line @enderror bg-white/70 px-4 py-3.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                            >
                            @include('partials.field-error', ['field' => 'password'])
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-sm text-charcoal font-medium cursor-pointer">
                                <input type="checkbox" name="remember" class="rounded border-line text-brand focus:ring-brand/30">
                                Remember me
                            </label>
                            <a href="{{ route('password.request') }}" class="text-sm font-bold text-brand hover:text-brand-hover">
                                Forgot password?
                            </a>
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-4 transition-all duration-150 hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                        >
                            <span>Log in</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>

                    <p class="lg:hidden text-center text-xs text-charcoal mt-6 font-medium">
                        Internal system. Authorized staff only.
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
