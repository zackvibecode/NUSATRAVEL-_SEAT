<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — Nusa Travel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/inter@5/index.min.css">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #faf7f7; color: #14100f; margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border: 1px solid #e8e4e2; border-radius: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 2rem; width: 100%; max-width: 24rem; }
        .logo { width: 44px; height: 44px; border-radius: 12px; overflow: hidden; margin: 0 auto 1.5rem; }
        h1 { font-size: 1.5rem; font-weight: 900; letter-spacing: -0.02em; margin: 0 0 0.5rem; text-align: center; }
        p.sub { color: #454245; font-size: 0.875rem; text-align: center; margin: 0 0 1.5rem; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; }
        input { width: 100%; box-sizing: border-box; border: 1px solid #e8e4e2; border-radius: 16px; padding: 0.875rem 1rem; font-size: 0.875rem; outline: none; transition: border-color 0.15s; margin-bottom: 1rem; }
        input:focus { border-color: #e4002b; }
        button { width: 100%; background: #e4002b; color: #fff; border: none; border-radius: 9999px; padding: 0.9rem; font-size: 0.875rem; font-weight: 700; cursor: pointer; margin-top: 0.25rem; }
        button:hover { background: #c40027; }
        .error { background: #fef2f2; color: #b91c1c; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; }
        .back { display: block; text-align: center; margin-top: 1.5rem; color: #454245; font-size: 0.875rem; font-weight: 700; text-decoration: none; }
        .back:hover { color: #14100f; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <svg class="w-full h-full" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" aria-label="Nusa Travel logo" role="img">
                <defs>
                    <linearGradient id="resetGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#ff1a3c"/>
                        <stop offset="100%" stop-color="#c40027"/>
                    </linearGradient>
                </defs>
                <circle cx="60" cy="60" r="60" fill="url(#resetGrad)"/>
                <text x="60" y="48" text-anchor="middle" dominant-baseline="central" font-family="'Inter', system-ui, sans-serif" font-weight="900" font-size="22" letter-spacing="-0.5" fill="#fff" font-style="italic">nusa</text>
                <text x="60" y="74" text-anchor="middle" dominant-baseline="central" font-family="'Inter', system-ui, sans-serif" font-weight="900" font-size="22" letter-spacing="-0.5" fill="#fff" font-style="italic">travel</text>
            </svg>
        </div>
        <h1>Reset password</h1>
        <p class="sub">Choose a new password for your account.</p>

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email">

            <label for="password">New password</label>
            <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Minimum 8 characters">

            <label for="password_confirmation">Confirm new password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">

            <button type="submit">Reset password</button>
        </form>

        <a href="{{ route('login') }}" class="back">&larr; Back to login</a>
    </div>
</body>
</html>
