<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Login — SeatWeb</title>

        @include('partials.assets')
    </head>
    <body class="bg-white text-ink antialiased min-h-screen">
        <div class="min-h-screen flex flex-col items-center justify-center p-6">
            <div class="w-full max-w-sm">
                {{-- Logo --}}
                <div class="flex items-center gap-2.5 mb-8">
                    <span class="flex items-center justify-center w-8 h-8 rounded-md bg-ink text-white font-bold text-sm">S</span>
                    <span class="font-semibold tracking-tight">SeatWeb</span>
                </div>

                <h1 class="text-2xl font-semibold tracking-tight mb-2">Log in</h1>
                <p class="text-sm text-charcoal mb-8">Enter your credentials to continue.</p>

                @if (session('status'))
                    <div class="mb-5 bg-positive-soft border border-positive/20 text-positive text-sm font-medium rounded-lg px-4 py-3">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 bg-brand-soft border border-brand/20 text-brand-ink text-sm rounded-lg px-4 py-3">
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-ink mb-1.5">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="you@company.com"
                            class="w-full rounded-lg border @error('email') border-red-400 @else border-line @enderror bg-white px-3 py-2.5 text-sm focus:border-ink focus:ring-2 focus:ring-ink/10 focus:outline-none transition-all"
                        >
                        @include('partials.field-error', ['field' => 'email'])
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-ink mb-1.5">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full rounded-lg border @error('password') border-red-400 @else border-line @enderror bg-white px-3 py-2.5 text-sm focus:border-ink focus:ring-2 focus:ring-ink/10 focus:outline-none transition-all"
                        >
                        @include('partials.field-error', ['field' => 'password'])
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-charcoal cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-line text-ink focus:ring-ink/20">
                            Remember me
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-ink hover:underline">
                            Forgot password?
                        </a>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-ink hover:bg-black text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors duration-150"
                    >
                        Log in
                    </button>
                </form>

                <p class="text-center text-xs text-charcoal mt-8">
                    Internal system. Authorized staff only.
                </p>
            </div>
        </div>
    </body>
</html>
