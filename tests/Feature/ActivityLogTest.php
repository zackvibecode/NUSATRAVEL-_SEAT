<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Departure;
use App\Models\Package;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Departure $departure;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $package = Package::create(['name' => 'PKG', 'destination' => 'DEST', 'status' => 'active']);
        $this->departure = Departure::create([
            'package_id' => $package->id,
            'departure_date' => now()->addMonth(),
            'return_date' => now()->addMonth()->addDays(3),
            'total_seats' => 20,
            'status' => 'open',
        ]);
    }

    public function test_creating_registration_is_logged(): void
    {
        $this->actingAs($this->admin)->post(route('registrations.store'), [
            'departure_id' => $this->departure->id,
            'name' => 'Ahmad',
            'pax' => 2,
            'payment_status' => 'pending',
        ])->assertRedirect();

        $log = ActivityLog::query()->where('action', 'created')->firstOrFail();

        $this->assertSame('registration', $log->subject_type);
        $this->assertSame('Ahmad', $log->subject_label);
        $this->assertSame($this->admin->id, $log->user_id);
        $this->assertSame('Ahmad', $log->changes['name']['new']);
        $this->assertSame('2', $log->changes['pax']['new']);
    }

    public function test_updating_registration_logs_the_diff(): void
    {
        $reg = Registration::create([
            'departure_id' => $this->departure->id,
            'name' => 'Ahmad',
            'pax' => 2,
        ]);

        $this->actingAs($this->admin)->put(route('registrations.update', $reg), [
            'name' => 'Ahmad Bin Ali',
            'pax' => 3,
            'payment_status' => 'deposit',
        ])->assertRedirect();

        $log = ActivityLog::query()->where('action', 'updated')->firstOrFail();

        $this->assertSame('Ahmad', $log->changes['name']['old']);
        $this->assertSame('Ahmad Bin Ali', $log->changes['name']['new']);
        $this->assertSame('2', $log->changes['pax']['old']);
        $this->assertSame('3', $log->changes['pax']['new']);
    }

    public function test_deleting_registration_is_logged(): void
    {
        $reg = Registration::create([
            'departure_id' => $this->departure->id,
            'name' => 'To Be Deleted',
            'pax' => 1,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('registrations.destroy', $reg))
            ->assertRedirect();

        $log = ActivityLog::query()->where('action', 'deleted')->firstOrFail();

        $this->assertSame('To Be Deleted', $log->subject_label);
        $this->assertSame('To Be Deleted', $log->changes['name']['old']);
        $this->assertSame('', $log->changes['name']['new']);
    }

    public function test_departure_and_package_changes_are_logged(): void
    {
        $package = Package::firstOrFail();

        $this->actingAs($this->admin)->put(route('packages.update', $package), [
            'name' => 'Renamed PKG',
            'destination' => 'DEST',
            'status' => 'active',
        ])->assertRedirect();

        $this->actingAs($this->admin)->put(route('departures.update', $this->departure), [
            'package_id' => $package->id,
            'departure_date' => now()->addMonth()->addDay()->toDateString(),
            'return_date' => now()->addMonth()->addDays(4)->toDateString(),
            'total_seats' => 35,
            'status' => 'open',
        ])->assertRedirect();

        $pkgLog = ActivityLog::query()->where('subject_type', 'package')->where('action', 'updated')->firstOrFail();
        $depLog = ActivityLog::query()->where('subject_type', 'departure')->where('action', 'updated')->firstOrFail();

        $this->assertSame('Renamed PKG', $pkgLog->changes['name']['new']);
        $this->assertSame('35', $depLog->changes['total_seats']['new']);
    }

    public function test_password_is_never_stored_in_logs(): void
    {
        $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'New Sales',
            'email' => 'newsales@test.test',
            'password' => 'super-secret-password',
            'role' => 'sales',
        ])->assertRedirect();

        $raw = ActivityLog::query()->latest('id')->first()->getRawOriginal('changes');
        $this->assertStringNotContainsString('super-secret-password', $raw);
        $this->assertArrayNotHasKey('password', ActivityLog::query()->latest('id')->first()->changes);
    }

    public function test_admin_can_view_activity_log_page(): void
    {
        Registration::create([
            'departure_id' => $this->departure->id,
            'name' => 'Logged Customer',
            'pax' => 1,
        ]);

        $this->actingAs($this->admin)
            ->post(route('registrations.store'), [
                'departure_id' => $this->departure->id,
                'name' => 'Another Customer',
                'pax' => 1,
            ])->assertRedirect();

        $response = $this->actingAs($this->admin)->get(route('activity-logs.index'));

        $response->assertOk()
            ->assertSee('Activity Log')
            ->assertSee($this->admin->name)
            ->assertSee('Another Customer');
    }

    public function test_sales_cannot_access_activity_log(): void
    {
        $sales = User::factory()->create(['role' => 'sales']);

        $this->actingAs($sales)
            ->get(route('activity-logs.index'))
            ->assertForbidden();
    }
}
