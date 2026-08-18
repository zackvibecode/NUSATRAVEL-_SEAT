<?php

namespace Tests\Feature;

use App\Mail\CapacityAlertMail;
use App\Models\Departure;
use App\Models\HermesSeatActivity;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CapacityAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_alert_emails_nearly_full_trips_only(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $package = Package::create(['name' => 'Makassar', 'destination' => 'Indonesia', 'status' => 'active']);
        $nearlyFull = $package->departures()->create([
            'departure_date' => now()->addDays(10)->toDateString(),
            'return_date' => now()->addDays(15)->toDateString(),
            'total_seats' => 10,
            'status' => 'open',
        ]);
        $nearlyFull->registrations()->create(['name' => 'Group', 'pax' => 8, 'need_partner' => false]);

        $plenty = $package->departures()->create([
            'departure_date' => now()->addDays(20)->toDateString(),
            'return_date' => now()->addDays(25)->toDateString(),
            'total_seats' => 40,
            'status' => 'open',
        ]);
        $plenty->registrations()->create(['name' => 'Solo', 'pax' => 1, 'need_partner' => false]);

        $this->artisan('seatweb:send-capacity-alerts')->assertExitCode(0);

        Mail::assertSent(CapacityAlertMail::class, 1);
        Mail::assertSent(CapacityAlertMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->trips->count() === 1
                && $mail->trips->first()->id === Departure::where('total_seats', 10)->first()->id;
        });
    }

    public function test_no_email_when_nothing_near_capacity(): void
    {
        Mail::fake();
        User::factory()->create();

        $this->artisan('seatweb:send-capacity-alerts')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_hermes_updates_page_filters_by_package_and_month(): void
    {
        $user = User::factory()->create();

        HermesSeatActivity::create([
            'package_name' => 'Makassar',
            'departure_date' => '2026-08-20',
            'seat_delta' => 5,
        ]);
        HermesSeatActivity::create([
            'package_name' => 'Yunnan',
            'departure_date' => '2026-09-25',
            'seat_delta' => -3,
        ]);

        $this->actingAs($user)
            ->get(route('hermes.updates', ['package' => 'makas']))
            ->assertOk()
            ->assertSee('Makassar')
            ->assertDontSee('Yunnan');

        $this->actingAs($user)
            ->get(route('hermes.updates', ['month' => 9]))
            ->assertOk()
            ->assertSee('Yunnan')
            ->assertDontSee('Makassar');
    }
}
