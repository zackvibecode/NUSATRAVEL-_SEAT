<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Registration;
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

    public function test_sales_cannot_add_edit_or_delete_registrations(): void
    {
        $package = Package::create(['name' => 'P', 'destination' => 'D', 'status' => 'active']);
        $departure = $package->departures()->create([
            'departure_date' => now()->addMonth(),
            'return_date' => now()->addMonth()->addDays(4),
            'total_seats' => 10,
            'status' => 'open',
        ]);
        $registration = Registration::create([
            'departure_id' => $departure->id,
            'name' => 'Existing',
            'pax' => 1,
        ]);

        $this->actingAs($this->sales)
            ->post(route('registrations.store'), [
                'departure_id' => $departure->id,
                'name' => 'Blocked',
                'pax' => 1,
            ])->assertForbidden();

        $this->actingAs($this->sales)
            ->put(route('registrations.update', $registration), [
                'name' => 'Hacked',
                'pax' => 1,
            ])->assertForbidden();

        $this->actingAs($this->sales)
            ->delete(route('registrations.destroy', $registration))
            ->assertForbidden();

        $this->assertDatabaseHas('registrations', ['id' => $registration->id, 'name' => 'Existing']);
        $this->assertDatabaseMissing('registrations', ['name' => 'Blocked']);
        $this->assertDatabaseMissing('registrations', ['name' => 'Hacked']);
    }

    public function test_sales_departure_page_hides_registration_buttons(): void
    {
        $package = Package::create(['name' => 'P', 'destination' => 'D', 'status' => 'active']);
        $departure = $package->departures()->create([
            'departure_date' => now()->addMonth(),
            'return_date' => now()->addMonth()->addDays(4),
            'total_seats' => 10,
            'status' => 'open',
        ]);
        Registration::create([
            'departure_id' => $departure->id,
            'name' => 'Customer',
            'pax' => 1,
        ]);

        $response = $this->actingAs($this->sales)->get(route('departures.show', $departure));

        $response->assertOk()
            ->assertDontSee('Add Registration')
            ->assertDontSee('Edit Trip')
            ->assertDontSee('data-edit-registration')
            ->assertDontSee('registrations.destroy')
            ->assertSee('Customer'); // still view-only data

        // Departures index hides the New Departure button too
        $this->actingAs($this->sales)
            ->get(route('departures.index'))
            ->assertOk()
            ->assertDontSee('New Departure');
    }

    public function test_admin_departure_page_shows_registration_buttons(): void
    {
        $package = Package::create(['name' => 'P', 'destination' => 'D', 'status' => 'active']);
        $departure = $package->departures()->create([
            'departure_date' => now()->addMonth(),
            'return_date' => now()->addMonth()->addDays(4),
            'total_seats' => 10,
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->admin)->get(route('departures.show', $departure));

        $response->assertOk()
            ->assertSee('Add Registration')
            ->assertSee('Edit Trip')
            ->assertSee('data-edit-registration');
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
