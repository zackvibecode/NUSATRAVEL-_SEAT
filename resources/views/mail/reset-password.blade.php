<x-mail::message>
# Reset your password

You requested a password reset for your SeatWeb account.

<x-mail::button :url="$url">
Reset Password
</x-mail::button>

This link expires in {{ $expires }}. If you didn't request a reset, no action is needed.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
