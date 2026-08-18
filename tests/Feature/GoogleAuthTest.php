<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function mockGoogleUser(string $email = 'zack@gmail.com', string $googleId = 'google-123'): void
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->id = $googleId;
        $abstractUser->name = 'Zack Google';
        $abstractUser->email = $email;
        $abstractUser->avatar = 'https://lh3.googleusercontent.com/photo.jpg';
        $abstractUser->shouldReceive('getId')->andReturn($googleId);
        $abstractUser->shouldReceive('getName')->andReturn('Zack Google');
        $abstractUser->shouldReceive('getEmail')->andReturn($email);
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/photo.jpg');

        Socialite::shouldReceive('driver->stateless->redirectUrl->user')->andReturn($abstractUser);
    }

    public function test_new_google_user_is_created_as_sales(): void
    {
        $this->mockGoogleUser();

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'zack@gmail.com')->firstOrFail();
        $this->assertSame('sales', $user->role);
        $this->assertSame('google-123', $user->google_id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_email_user_is_linked_not_duplicated(): void
    {
        // Admin manually created this account earlier with a password
        $existing = User::factory()->create([
            'email' => 'zack@gmail.com',
            'role' => 'admin',
        ]);

        $this->mockGoogleUser();

        $this->get(route('google.callback'))->assertRedirect(route('dashboard'));

        $this->assertSame(1, User::where('email', 'zack@gmail.com')->count());
        $existing->refresh();
        $this->assertSame('google-123', $existing->google_id);
        $this->assertSame('admin', $existing->role); // role preserved, never downgraded
        $this->assertAuthenticatedAs($existing);
    }

    public function test_returning_google_user_logs_in_directly(): void
    {
        $user = User::factory()->create([
            'email' => 'zack@gmail.com',
            'google_id' => 'google-123',
            'role' => 'sales',
        ]);

        $this->mockGoogleUser();

        $this->get(route('google.callback'))->assertRedirect(route('dashboard'));

        $this->assertSame(1, User::count());
        $this->assertAuthenticatedAs($user);
    }

    public function test_callback_failure_redirects_to_login_with_error(): void
    {
        Socialite::shouldReceive('driver->stateless->redirectUrl->user')->andThrow(new \Exception('OAuth failed'));

        $this->get(route('google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_google_login_route_redirects_to_google(): void
    {
        config([
            'services.google.client_id' => 'test-id',
            'services.google.client_secret' => 'test-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        $this->get(route('google.login'))
            ->assertRedirect()
            ->assertSee('accounts.google.com', false);
    }

    public function test_google_redirect_uri_uses_current_host_not_stale_env(): void
    {
        config([
            'app.url' => 'https://nusatravel-seat.onrender.com',
            'services.google.client_id' => 'test-id',
            'services.google.client_secret' => 'test-secret',
            // Stale Render env that used to poison production logins
            'services.google.redirect' => 'http://localhost:8000/auth/google/callback',
        ]);

        $this->app['env'] = 'production';

        $response = $this->get('/auth/google');

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertStringContainsString('accounts.google.com', $location);

        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame(
            'https://nusatravel-seat.onrender.com/auth/google/callback',
            $query['redirect_uri'] ?? null,
        );
        $this->assertStringNotContainsString('localhost', $query['redirect_uri'] ?? '');
    }

    public function test_owner_email_is_always_admin(): void
    {
        // Owner email must be admin even if the stored role says sales
        $owner = User::factory()->create([
            'email' => 'zarulzaqwan5678@gmail.com',
            'role' => 'sales',
        ]);

        $this->assertTrue($owner->isAdmin());

        // New Google sign-in with the owner email creates an admin account
        $this->mockGoogleUser('zarulzaqwan5678@gmail.com', 'owner-1');

        $this->get(route('google.callback'))->assertRedirect(route('dashboard'));

        $owner->refresh();
        $this->assertSame('admin', $owner->role);
        $this->assertTrue($owner->isAdmin());
    }
}
