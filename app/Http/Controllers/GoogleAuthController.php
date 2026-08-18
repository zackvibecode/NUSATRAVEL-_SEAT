<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     *
     * The redirect URI is computed from the current request host so a stale
     * GOOGLE_REDIRECT_URI=http://localhost:... env value on Render cannot
     * poison production logins.
     */
    public function redirect(): RedirectResponse
    {
        return $this->googleDriver()->redirect();
    }

    /**
     * Handle the OAuth callback. Existing users are matched by email
     * (or google_id); new Gmail users are auto-created as sales.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = $this->googleDriver()->user();
        } catch (Throwable $e) {
            Log::warning('Google sign-in failed', [
                'message' => $e->getMessage(),
                'callback_url' => $this->callbackUrl(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google sign-in failed. Please try again or use email + password.']);
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));

        if ($email === '') {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Your Google account did not return an email address.']);
        }

        try {
            $user = $this->findOrCreateFromGoogle($googleUser, $email);
        } catch (Throwable $e) {
            Log::error('Google sign-in could not save the user', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google sign-in failed while creating your account. Please try again.']);
        }

        Auth::login($user, remember: true);
        session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * @param  \Laravel\Socialite\Contracts\User  $googleUser
     */
    private function findOrCreateFromGoogle(object $googleUser, string $email): User
    {
        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        $shouldBeAdmin = in_array($email, User::adminEmails(), true);
        $avatar = Str::limit((string) $googleUser->getAvatar(), 500, '');

        if (! $user) {
            return User::create([
                'name' => $googleUser->getName() ?: $email,
                'email' => $email,
                'role' => $shouldBeAdmin ? 'admin' : 'sales',
                'google_id' => $googleUser->getId(),
                'avatar' => $avatar ?: null,
                'password' => Str::password(32),
                'email_verified_at' => now(),
            ]);
        }

        $user->update([
            'google_id' => $user->google_id ?: $googleUser->getId(),
            'avatar' => $user->avatar ?: ($avatar ?: null),
            'email_verified_at' => $user->email_verified_at ?: now(),
            'role' => $shouldBeAdmin && $user->role !== 'admin' ? 'admin' : $user->role,
        ]);

        return $user;
    }

    /**
     * @return Provider
     */
    private function googleDriver()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirectUrl($this->callbackUrl());
    }

    /**
     * Never send localhost to Google from production — a stale
     * GOOGLE_REDIRECT_URI on Render was causing Error 400.
     */
    private function callbackUrl(): string
    {
        $configured = (string) config('services.google.redirect');
        if ($this->isPublicHttpUrl($configured)) {
            return $configured;
        }

        $request = request();
        $host = (string) $request->getHost();

        if (! $this->isLocalHost($host)) {
            return 'https://'.$host.'/auth/google/callback';
        }

        $appHost = (string) parse_url((string) config('app.url'), PHP_URL_HOST);
        if ($appHost !== '' && ! $this->isLocalHost($appHost)) {
            return 'https://'.$appHost.'/auth/google/callback';
        }

        if (app()->environment('production')) {
            return 'https://nusatravel-seat.onrender.com/auth/google/callback';
        }

        return $request->getSchemeAndHttpHost().'/auth/google/callback';
    }

    private function isLocalHost(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    private function isPublicHttpUrl(string $url): bool
    {
        if (! str_starts_with($url, 'https://') && ! str_starts_with($url, 'http://')) {
            return false;
        }

        $host = (string) parse_url($url, PHP_URL_HOST);

        return $host !== '' && ! $this->isLocalHost($host);
    }
}
