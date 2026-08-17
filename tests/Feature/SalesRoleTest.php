<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesRoleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $sales;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->sales = User::factory()->create(['role' => 'sales']);
    }

    public function test_sales_gets_sales_dashboard(): void
    {
        $response = $this->actingAs($this->sales)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Welcome back, '.$this->sales->name)
            ->assertSee('Seats Available')
            ->assertDontSee('Pax Trend');
    }

    public function test_admin_gets_regular_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Pax Trend')
            ->assertDontSee('Welcome back,');
    }

    public function test_sales_cannot_access_reports(): void
    {
        $this->actingAs($this->sales)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($this->sales)->get(route('reports.export'))->assertForbidden();
    }

    public function test_sales_cannot_access_packages(): void
    {
        $this->actingAs($this->sales)->get(route('packages.index'))->assertForbidden();
        $this->actingAs($this->sales)->get(route('packages.create'))->assertForbidden();
    }

    public function test_sales_cannot_access_users_management(): void
    {
        $this->actingAs($this->sales)->get(route('users.index'))->assertForbidden();
        $this->actingAs($this->sales)->get(route('users.create'))->assertForbidden();
    }

    public function test_sales_cannot_access_hermes_chat(): void
    {
        $this->actingAs($this->sales)->get(route('hermes.chat'))->assertForbidden();
    }

    public function test_sales_cannot_create_or_edit_departures(): void
    {
        $this->actingAs($this->sales)->get(route('departures.create'))->assertForbidden();
    }

    public function test_sales_can_view_trips_and_updates(): void
    {
        $this->actingAs($this->sales)->get(route('departures.index'))->assertOk();
        $this->actingAs($this->sales)->get(route('hermes.updates'))->assertOk();
        $this->actingAs($this->sales)->get(route('participants.index'))->assertOk();
        $this->actingAs($this->sales)->get(route('calendar.index'))->assertOk();
    }

    public function test_sales_sidebar_hides_admin_links(): void
    {
        $response = $this->actingAs($this->sales)->get(route('dashboard'));

        $response->assertOk()
            ->assertDontSee('href="'.route('reports.index').'"', false)
            ->assertDontSee('href="'.route('users.index').'"', false)
            ->assertDontSee('href="'.route('packages.index').'"', false)
            ->assertDontSee('href="'.route('hermes.chat').'"', false);
    }

    public function test_admin_sidebar_shows_admin_links(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('href="'.route('reports.index').'"', false)
            ->assertSee('href="'.route('users.index').'"', false);
    }

    public function test_admin_can_create_sales_user(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Ali Sales',
            'email' => 'ali.sales@example.com',
            'password' => 'password123',
            'role' => 'sales',
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'ali.sales@example.com',
            'role' => 'sales',
        ]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $this->admin));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_existing_users_default_to_admin_role(): void
    {
        $this->assertTrue($this->admin->isAdmin());
        $this->assertTrue($this->sales->isSales());
    }
}
