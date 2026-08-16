<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Login — SeatWeb</title>

        @include('partials.assets')
    </head>
    <body class="bg-white text-ink antialiased min-h-screen">
        <div class="min-h-screen flex flex-col lg:flex-row">
            <!-- Left panel — brand showcase with travel destination image -->
            <div class="lg:w-1/2 relative overflow-hidden flex flex-col justify-between p-8 lg:p-12 text-white">
                <!-- Background image with brand overlay -->
                <div class="absolute inset-0">
                    <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?q=80&w=1600&auto=format&fit=crop"
                         alt="" aria-hidden="true"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-brand/80"></div>
                </div>

                <div class="relative z-10">
                    <a href="#" class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-12 h-12 rounded-full bg-white text-brand font-black text-xl shadow-lg">S</span>
                        <span class="text-2xl font-black tracking-tight">SeatWeb</span>
                    </a>
                </div>

                <div class="relative z-10 max-w-md">
                    <h1 class="text-5xl lg:text-6xl font-black tracking-tight leading-[0.95]">
                        Trip Seat<br>Availability
                    </h1>
                    <p class="text-white/85 text-lg mt-6 font-medium leading-relaxed">
                        SeatWeb helps your travel agency track how many pax are registered and how many seats remain — manage tour capacity with clarity.
                    </p>

                    <div class="mt-10 space-y-4">
                        <div class="flex items-center gap-4 bg-white/15 backdrop-blur rounded-2xl p-4">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">Real-time seat tracking</p>
                                <p class="text-white/75 text-xs mt-0.5">See capacity and occupancy instantly</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 bg-white/15 backdrop-blur rounded-2xl p-4">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">Partner matching</p>
                                <p class="text-white/75 text-xs mt-0.5">Find room-sharing partners easily</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 bg-white/15 backdrop-blur rounded-2xl p-4">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">Monthly reports</p>
                                <p class="text-white/75 text-xs mt-0.5">Generate insights in one click</p>
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
                        <span class="flex items-center justify-center w-14 h-14 rounded-full bg-brand text-white font-black text-2xl mx-auto shadow-lg">S</span>
                        <h1 class="text-3xl font-black tracking-tight mt-4">Trip Seat Availability</h1>
                    </div>

                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-line">
                        <div class="mb-8">
                            <span class="hidden lg:flex items-center justify-center w-14 h-14 rounded-full bg-brand text-white font-black text-2xl shadow-lg mb-6">S</span>
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
                                    class="w-full rounded-2xl border @error('email') border-red-400 @else border-line @enderror bg-white px-4 py-3.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
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
                                    class="w-full rounded-2xl border @error('password') border-red-400 @else border-line @enderror bg-white px-4 py-3.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
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
                                class="w-full bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-4 transition-all duration-150 hover:scale-[1.02] shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                            >
                                <span>Log in</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <p class="text-center text-xs text-charcoal mt-6 font-medium">
                        Internal system. Authorized staff only.
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
