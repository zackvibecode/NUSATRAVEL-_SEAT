<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password — SeatWeb</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/inter@5/index.min.css">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #faf7f7; color: #14100f; margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border: 1px solid #e8e4e2; border-radius: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 2rem; width: 100%; max-width: 24rem; }
        .logo { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #ff1a3c 0%, #c40027 100%); color: #fff; font-weight: 700; font-size: 0.85rem; letter-spacing: 1px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        h1 { font-size: 1.5rem; font-weight: 900; letter-spacing: -0.02em; margin: 0 0 0.5rem; text-align: center; }
        p.sub { color: #454245; font-size: 0.875rem; text-align: center; margin: 0 0 1.5rem; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; }
        input { width: 100%; box-sizing: border-box; border: 1px solid #e8e4e2; border-radius: 16px; padding: 0.875rem 1rem; font-size: 0.875rem; outline: none; transition: border-color 0.15s; }
        input:focus { border-color: #e4002b; }
        button { width: 100%; background: #e4002b; color: #fff; border: none; border-radius: 9999px; padding: 0.9rem; font-size: 0.875rem; font-weight: 700; cursor: pointer; margin-top: 1.25rem; }
        button:hover { background: #c40027; }
        .status { background: #e5f5ec; color: #1e7d46; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; }
        .error { background: #fef2f2; color: #b91c1c; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; }
        .back { display: block; text-align: center; margin-top: 1.5rem; color: #454245; font-size: 0.875rem; font-weight: 700; text-decoration: none; }
        .back:hover { color: #14100f; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">SW</div>
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
