<?php

namespace Tests\Feature;

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

        $this->assertDatabaseMissing('registrations', ['id' => $reg->json('id')]);

        $this->withToken($this->token)
            ->deleteJson('/api/hermes/packages/'.$id)
            ->assertOk();

        $this->assertSame('archived', Package::find($id)->status);
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

    public function test_chat_deletes_pax(): void
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

        $this->actingAs($user)
            ->postJson(route('hermes.chat.message'), ['message' => 'padam pax '.$reg->id])
            ->assertOk()
            ->assertJsonPath('action', 'delete_registration');

        $this->assertDatabaseMissing('registrations', ['id' => $reg->id]);
    }
}
