<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function requestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a reset link (always responds the same way to avoid user enumeration).
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->string('email')->lower())->first();

        if ($user) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($token), 'created_at' => now()],
            );

            Mail::to($user->email)->send(new \App\Mail\ResetPasswordMail($token, $user->email));
        }

        return back()->with('status', 'If that email exists in our system, a reset link has been sent. Check your inbox.');
    }

    /**
     * Show the reset form.
     */
    public function resetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Reset the password.
     */
    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        $valid = $record
            && Hash::check($data['token'], $record->token)
            && \Illuminate\Support\Carbon::parse($record->created_at)->gt(now()->subHour());

        if (! $valid) {
            return back()->withInput()->withErrors(['email' => 'This password reset link is invalid or has expired.']);
        }

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return back()->withInput()->withErrors(['email' => 'This password reset link is invalid or has expired.']);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()->route('login')->with('status', 'Password reset successfully. You can now log in.');
    }
}
