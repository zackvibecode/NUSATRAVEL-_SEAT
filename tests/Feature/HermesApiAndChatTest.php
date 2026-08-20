<?php

namespace Tests\Feature;

use App\Models\HermesSeatActivity;
use App\Models\Package;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HermesApiAndChatTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-import-token-seatweb';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.import.token' => $this->token]);
    }

    public function test_hermes_can_crud_package_and_delete_registration(): void
    {
        $create = $this->withToken($this->token)->postJson('/api/hermes/packages', [
            'name' => 'TRANSJAVA',
            'destination' => 'INDONESIA',
        ]);
        $create->assertCreated();
        $id = $create->json('id');

        $this->withToken($this->token)
            ->getJson('/api/hermes/packages/'.$id)
            ->assertOk()
            ->assertJsonPath('name', 'TRANSJAVA');

        $this->withToken($this->token)
            ->putJson('/api/hermes/packages/'.$id, ['description' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('description', 'Updated');

        $dep = $this->withToken($this->token)->postJson('/api/hermes/departures', [
            'package_id' => $id,
            'departure_date' => '2026-09-15',
            'return_date' => '2026-09-22',
            'total_seats' => 10,
        ])->assertCreated();

        $reg = $this->withToken($this->token)->postJson('/api/hermes/registrations', [
            'departure_id' => $dep->json('id'),
            'name' => 'Ahmad',
            'pax' => 2,
        ])->assertCreated();

        $this->withToken($this->token)
            ->deleteJson('/api/hermes/registrations/'.$reg->json('id'))
            ->assertOk();

        $this->assertSoftDeleted('registrations', ['id' => $reg->json('id')]);

        $this->withToken($this->token)
            ->deleteJson('/api/hermes/packages/'.$id)
            ->assertOk();

        $this->assertDatabaseMissing('packages', ['id' => $id]);
    }

    public function test_staff_chatbot_lists_packages(): void
    {
        $user = User::factory()->create();
        Package::create([
            'name' => 'TRANSJAVA',
            'destination' => 'INDONESIA',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->postJson(route('hermes.chat.message'), ['message' => 'list package'])
            ->assertOk()
            ->assertJsonPath('action', 'list_packages')
            ->assertSee('TRANSJAVA');
    }

    public function test_chat_destructive_commands_require_confirmation(): void
    {
        $user = User::factory()->create();
        $package = Package::create(['name' => 'X', 'destination' => 'Y', 'status' => 'active']);
        $dep = $package->departures()->create([
            'departure_date' => '2026-09-15',
            'return_date' => '2026-09-22',
            'total_seats' => 10,
            'status' => 'open',
        ]);
        $reg = Registration::create([
            'departure_id' => $dep->id,
            'name' => 'Siti',
            'pax' => 1,
            'need_partner' => false,
        ]);

        // Step 1: destructive intent without "confirm" must NOT delete anything.
        $this->actingAs($user)
            ->postJson(route('hermes.chat.message'), ['message' => 'padam pax '.$reg->id])
            ->assertOk()
            ->assertJsonPath('action', 'confirm_required');

        $this->assertNotSoftDeleted('registrations', ['id' => $reg->id]);

        // Step 2: explicit confirm executes the deletion.
        $this->actingAs($user)
            ->postJson(route('hermes.chat.message'), ['message' => 'confirm padam pax '.$reg->id])
            ->assertOk()
            ->assertJsonPath('action', 'delete_registration');

        $this->assertSoftDeleted('registrations', ['id' => $reg->id]);
    }

    public function test_chat_confirmed_package_delete_cascades(): void
    {
        $user = User::factory()->create();
        $package = Package::create(['name' => 'Z', 'destination' => 'W', 'status' => 'active']);

        $this->actingAs($user)
            ->postJson(route('hermes.chat.message'), ['message' => 'delete package '.$package->id])
            ->assertOk()
            ->assertJsonPath('action', 'confirm_required');

        $this->assertDatabaseHas('packages', ['id' => $package->id]);

        $this->actingAs($user)
            ->postJson(route('hermes.chat.message'), ['message' => 'confirm delete package '.$package->id])
            ->assertOk()
            ->assertJsonPath('action', 'delete_package');

        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
    }

    public function test_updating_total_seats_records_available_seat_activity(): void
    {
        $package = $this->withToken($this->token)->postJson('/api/hermes/packages', [
            'name' => 'Yunnan',
            'destination' => 'China',
        ])->assertCreated();

        HermesSeatActivity::query()->delete();

        $dep = $this->withToken($this->token)->postJson('/api/hermes/departures', [
            'package_id' => $package->json('id'),
            'departure_date' => '2026-08-25',
            'return_date' => '2026-08-30',
            'total_seats' => 10,
        ])->assertCreated();

        $created = HermesSeatActivity::firstOrFail();
        $this->assertSame('Yunnan', $created->package_name);
        $this->assertTrue($created->departure_date->isSameDay('2026-08-25'));
        $this->assertSame(10, $created->seat_delta);
        $this->assertSame('departure_created', $created->activity_type);
        $this->assertStringContainsString('Total seats: 10', $created->activity_note ?? '');

        HermesSeatActivity::query()->delete();

        $this->withToken($this->token)
            ->putJson('/api/hermes/departures/'.$dep->json('id'), ['total_seats' => 7])
            ->assertOk();

        $this->assertDatabaseCount('hermes_seat_activities', 1);
        $updated = HermesSeatActivity::firstOrFail();
        $this->assertSame('Yunnan', $updated->package_name);
        $this->assertTrue($updated->departure_date->isSameDay('2026-08-25'));
        $this->assertSame(-3, $updated->seat_delta);
        $this->assertSame('departure_updated', $updated->activity_type);
        $this->assertStringContainsString('10->7', $updated->activity_note ?? '');
    }

    public function test_registration_pax_change_records_seat_activity(): void
    {
        $package = $this->withToken($this->token)->postJson('/api/hermes/packages', [
            'name' => 'Chengdu',
            'destination' => 'China',
        ])->assertCreated();

        $dep = $this->withToken($this->token)->postJson('/api/hermes/departures', [
            'package_id' => $package->json('id'),
            'departure_date' => '2026-09-02',
            'return_date' => '2026-09-08',
            'total_seats' => 20,
        ])->assertCreated();

        HermesSeatActivity::query()->delete();

        $reg = $this->withToken($this->token)->postJson('/api/hermes/registrations', [
            'departure_id' => $dep->json('id'),
            'name' => 'Ahmad',
            'pax' => 3,
            'activity_note' => 'Didaftar oleh agent Zack',
        ])->assertCreated();

        $activity = HermesSeatActivity::firstOrFail();
        $this->assertSame('Chengdu', $activity->package_name);
        $this->assertTrue($activity->departure_date->isSameDay('2026-09-02'));
        $this->assertSame(3, $activity->seat_delta);
        $this->assertSame('registration_created', $activity->activity_type);
        $this->assertSame('Ahmad', $activity->actor_name);
        $this->assertSame('Didaftar oleh agent Zack', $activity->activity_note);

        HermesSeatActivity::query()->delete();

        $this->withToken($this->token)
            ->putJson('/api/hermes/registrations/'.$reg->json('id'), ['pax' => 3])
            ->assertOk();

        $this->assertDatabaseCount('hermes_seat_activities', 0);
    }
}
