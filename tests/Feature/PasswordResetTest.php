<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_renders(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Forgot password');
    }

    public function test_reset_link_is_sent_for_existing_user(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'staff@seatweb.com']);

        $this->post(route('password.email'), ['email' => 'staff@seatweb.com'])
            ->assertRedirect()
            ->assertSessionHas('status');

        Mail::assertQueued(ResetPasswordMail::class, fn ($mail) => $mail->hasTo('staff@seatweb.com'));
        $this->assertDatabaseCount('password_reset_tokens', 1);
    }

    public function test_reset_link_does_not_reveal_missing_users(): void
    {
        Mail::fake();

        $this->post(route('password.email'), ['email' => 'nobody@seatweb.com'])
            ->assertRedirect()
            ->assertSessionHas('status');

        Mail::assertNothingQueued();
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        $token = '';
        Mail::assertQueued(ResetPasswordMail::class, function ($mail) use (&$token) {
            $token = $mail->token;

            return true;
        });

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertTrue(Hash::check('new-secret-123', $user->fresh()->password));
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_expired_token_is_rejected(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('stale-token'),
            'created_at' => now()->subHours(3),
        ]);

        $this->post(route('password.update'), [
            'token' => 'stale-token',
            'email' => $user->email,
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
