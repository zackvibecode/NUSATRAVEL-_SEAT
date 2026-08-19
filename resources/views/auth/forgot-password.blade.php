<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password — Nusa Travel</title>
    @include('partials.theme-boot')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/inter@5/index.min.css">
    <style>
        :root { --app-fog:#fafafa; --app-surface:#fff; --app-ink:#14100f; --app-charcoal:#454245; --app-line:#e8e4e2; --app-brand:#e4002b; --app-brand-hover:#c40027; color-scheme:light; }
        html.dark { --app-fog:#07111f; --app-surface:#0f1c33; --app-ink:#e8eef8; --app-charcoal:#9bb0cc; --app-line:#24385c; --app-brand:#ff4d6d; --app-brand-hover:#ff6b85; color-scheme:dark; }
        html.dark .theme-icon-sun { display:none; }
        html:not(.dark) .theme-icon-moon { display:none; }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--app-fog); color: var(--app-ink); margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: var(--app-surface); border: 1px solid var(--app-line); border-radius: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 2rem; width: 100%; max-width: 24rem; }
        .logo { width: 56px; height: 56px; border-radius: 9999px; overflow: hidden; margin: 0 auto 1.5rem; background: #000; }
        .logo img { width: 100%; height: 100%; object-fit: cover; transform: scale(1.28); }
        h1 { font-size: 1.5rem; font-weight: 900; letter-spacing: -0.02em; margin: 0 0 0.5rem; text-align: center; }
        p.sub { color: var(--app-charcoal); font-size: 0.875rem; text-align: center; margin: 0 0 1.5rem; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; }
        input { width: 100%; box-sizing: border-box; border: 1px solid var(--app-line); border-radius: 16px; padding: 0.875rem 1rem; font-size: 0.875rem; outline: none; transition: border-color 0.15s; background: var(--app-surface); color: var(--app-ink); }
        input:focus { border-color: var(--app-brand); }
        button[type=submit] { width: 100%; background: var(--app-brand); color: #fff; border: none; border-radius: 9999px; padding: 0.9rem; font-size: 0.875rem; font-weight: 700; cursor: pointer; margin-top: 1.25rem; }
        button[type=submit]:hover { background: var(--app-brand-hover); }
        .status { background: #e5f5ec; color: #1e7d46; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; }
        .error { background: #fef2f2; color: #b91c1c; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; }
        html.dark .status { background: #0f2a1c; color: #4ade80; }
        html.dark .error { background: rgba(127,29,29,0.35); color: #fca5a5; }
        .back { display: block; text-align: center; margin-top: 1.5rem; color: var(--app-charcoal); font-size: 0.875rem; font-weight: 700; text-decoration: none; }
        .back:hover { color: var(--app-ink); }
        .theme-wrap { position: fixed; top: 1rem; right: 1rem; }
        .theme-wrap button { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--app-line); background: var(--app-surface); color: var(--app-ink); border-radius: 9999px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="theme-wrap">
        @include('partials.theme-toggle', ['compact' => true])
    </div>
    <div class="card">
        <div class="logo">
            <img src="{{ asset('logo-nusa.png') }}" alt="Nusa Travel" width="56" height="56">
        </div>
        <h1>Forgot password</h1>
        <p class="sub">Enter your email and we'll send you a reset link.</p>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@company.com">
            <button type="submit">Send reset link</button>
        </form>

        <a href="{{ route('login') }}" class="back">&larr; Back to login</a>
    </div>
</body>
</html>
