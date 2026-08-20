<?php

namespace Tests\Feature;

use App\Models\HermesSeatActivity;
use App\Models\Departure;
use App\Models\Package;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationCapacityTest extends TestCase
{
    use RefreshDatabase;

    private function seedDeparture(int $totalSeats): Departure
    {
        $package = Package::create([
            'name' => 'TRANSJAVA',
            'destination' => 'INDONESIA',
            'status' => 'active',
        ]);

        return $package->departures()->create([
            'departure_date' => '2026-09-15',
            'return_date' => '2026-09-22',
            'total_seats' => $totalSeats,
            'status' => 'open',
        ]);
    }

    public function test_store_rejects_pax_over_capacity(): void
    {
        $user = User::factory()->create();
        $departure = $this->seedDeparture(5);

        $response = $this->actingAs($user)->post(route('registrations.store'), [
            'departure_id' => $departure->id,
            'name' => 'Ahmad',
            'pax' => 6,
            'payment_status' => 'pending',
        ]);

        $response->assertSessionHasErrors('pax');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_store_rejects_when_remaining_seats_are_insufficient(): void
    {
        $user = User::factory()->create();
        $departure = $this->seedDeparture(5);

        Registration::create([
            'departure_id' => $departure->id,
            'name' => 'Existing',
            'pax' => 3,
        ]);

        $response = $this->actingAs($user)->post(route('registrations.store'), [
            'departure_id' => $departure->id,
            'name' => 'Ahmad',
            'pax' => 3,
            'payment_status' => 'pending',
        ]);

        $response->assertSessionHasErrors('pax');
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_store_allows_exactly_remaining_seats(): void
    {
        $user = User::factory()->create();
        $departure = $this->seedDeparture(5);

        Registration::create([
            'departure_id' => $departure->id,
            'name' => 'Existing',
            'pax' => 3,
        ]);

        $this->actingAs($user)->post(route('registrations.store'), [
            'departure_id' => $departure->id,
            'name' => 'Ahmad',
            'pax' => 2,
            'payment_status' => 'paid',
        ])->assertRedirect(route('departures.show', $departure));

        $this->assertDatabaseCount('registrations', 2);
        $this->assertSame(5, (int) Registration::sum('pax'));
    }

    public function test_update_cannot_exceed_capacity(): void
    {
        $user = User::factory()->create();
        $departure = $this->seedDeparture(5);

        $a = Registration::create(['departure_id' => $departure->id, 'name' => 'A', 'pax' => 3]);
        $b = Registration::create(['departure_id' => $departure->id, 'name' => 'B', 'pax' => 1]);

        $response = $this->actingAs($user)->put(route('registrations.update', $b), [
            'name' => 'B',
            'pax' => 3,
            'payment_status' => 'pending',
        ]);

        $response->assertSessionHasErrors('pax');
        $this->assertSame(1, $b->fresh()->pax);
    }

    public function test_store_logs_hermes_seat_activity(): void
    {
        $user = User::factory()->create();
        $departure = $this->seedDeparture(10);

        $this->actingAs($user)->post(route('registrations.store'), [
            'departure_id' => $departure->id,
            'name' => 'Ahmad Sufian',
            'pax' => 2,
            'payment_status' => 'pending',
        ])->assertRedirect();

        $activity = HermesSeatActivity::query()->where('actor_name', 'Ahmad Sufian')->firstOrFail();

        $this->assertSame('registration_created', $activity->activity_type);
        $this->assertSame(2, $activity->seat_delta);
        $this->assertSame('TRANSJAVA', $activity->package_name);
    }

    public function test_update_logs_hermes_seat_activity_delta(): void
    {
        $user = User::factory()->create();
        $departure = $this->seedDeparture(10);

        $reg = Registration::create(['departure_id' => $departure->id, 'name' => 'A', 'pax' => 2]);

        $this->actingAs($user)->put(route('registrations.update', $reg), [
            'name' => 'A',
            'pax' => 4,
            'payment_status' => 'pending',
        ])->assertRedirect();

        $activity = HermesSeatActivity::query()->where('actor_name', 'A')->firstOrFail();

        $this->assertSame('registration_updated', $activity->activity_type);
        $this->assertSame(2, $activity->seat_delta); // 4 - 2
    }

    public function test_destroy_logs_hermes_seat_activity_cancellation(): void
    {
        $user = User::factory()->create();
        $departure = $this->seedDeparture(10);

        $reg = Registration::create(['departure_id' => $departure->id, 'name' => 'A', 'pax' => 3]);

        $this->actingAs($user)->delete(route('registrations.destroy', $reg))->assertRedirect();

        $activity = HermesSeatActivity::query()->where('actor_name', 'A')->firstOrFail();

        $this->assertSame('registration_deleted', $activity->activity_type);
        $this->assertSame(-3, $activity->seat_delta);
    }
}
