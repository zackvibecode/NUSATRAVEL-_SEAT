<?php

namespace Tests\Feature;

use App\Models\Departure;
use App\Models\Package;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAlertTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $picZack;

    private User $picAina;

    private Departure $departure;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->picZack = User::factory()->create(['role' => 'sales', 'pic_name' => 'Zack']);
        $this->picAina = User::factory()->create(['role' => 'sales', 'name' => 'Aina Kamal']);

        $package = Package::create([
            'name' => 'Makassar 5D4N',
            'destination' => 'Indonesia',
            'status' => 'active',
        ]);

        $this->departure = Departure::create([
            'package_id' => $package->id,
            'departure_date' => now()->addMonth(),
            'return_date' => now()->addMonth()->addDays(4),
            'total_seats' => 25,
            'status' => 'open',
        ]);
    }

    private function registration(array $overrides = []): Registration
    {
        return Registration::create(array_merge([
            'departure_id' => $this->departure->id,
            'name' => 'Test Customer',
            'pax' => 1,
        ], $overrides));
    }

    public function test_zero_paid_with_balance_is_belum_bayar(): void
    {
        $reg = $this->registration([
            'invoice_no' => 'INV-001',
            'invoice_amount' => 1000,
            'total_paid' => 0,
        ]);

        $this->assertSame('belum_bayar', $reg->derived_payment_status);
        $this->assertSame(1000.0, $reg->balance);
        $this->assertTrue($reg->requires_follow_up);
    }

    public function test_deposit_paid_still_balance_is_partial(): void
    {
        $reg = $this->registration([
            'invoice_no' => 'INV-002',
            'invoice_amount' => 1000,
            'total_paid' => 300,
        ]);

        $this->assertSame('partial', $reg->derived_payment_status);
        $this->assertSame(700.0, $reg->balance);
        $this->assertTrue($reg->requires_follow_up);
    }

    public function test_zero_balance_is_paid(): void
    {
        $reg = $this->registration([
            'invoice_no' => 'INV-003',
            'invoice_amount' => 1000,
            'total_paid' => 1000,
        ]);

        $this->assertSame('paid', $reg->derived_payment_status);
        $this->assertSame(0.0, $reg->balance);
        $this->assertFalse($reg->requires_follow_up);
    }

    public function test_cancelled_invoice_status_wins(): void
    {
        $reg = $this->registration([
            'invoice_no' => 'INV-004',
            'invoice_status' => 'Cancelled',
            'invoice_amount' => 1000,
            'total_paid' => 0,
        ]);

        $this->assertSame('cancelled', $reg->derived_payment_status);
        $this->assertFalse($reg->requires_follow_up);
    }

    public function test_manual_pending_registration_is_belum_bayar(): void
    {
        // No invoice data — created manually via the admin form
        $reg = $this->registration([
            'payment_status' => 'pending',
        ]);

        $this->assertSame('belum_bayar', $reg->derived_payment_status);
        $this->assertTrue($reg->requires_follow_up);
    }

    public function test_manual_deposit_registration_is_partial(): void
    {
        $reg = $this->registration([
            'payment_status' => 'deposit',
        ]);

        $this->assertSame('partial', $reg->derived_payment_status);
        $this->assertTrue($reg->requires_follow_up);
    }

    public function test_manual_paid_registration_is_paid(): void
    {
        $reg = $this->registration([
            'payment_status' => 'paid',
        ]);

        $this->assertSame('paid', $reg->derived_payment_status);
        $this->assertFalse($reg->requires_follow_up);
    }

    public function test_status_filter_paid_excludes_manual_pending(): void
    {
        $this->registration(['name' => 'Manual Pending', 'payment_status' => 'pending']);
        $this->registration(['name' => 'Invoice Paid', 'invoice_amount' => 500, 'total_paid' => 500]);

        $response = $this->actingAs($this->admin)
            ->get(route('payment-alerts.index', ['status' => 'paid']));

        $response->assertOk()
            ->assertSee('Invoice Paid')
            ->assertDontSee('Manual Pending');
    }

    public function test_search_works_without_crashing(): void
    {
        $this->registration([
            'name' => 'Zack Customer',
            'pic_utama' => 'Zack',
            'invoice_no' => 'INV-SEARCH-1',
            'invoice_amount' => 500,
            'total_paid' => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('payment-alerts.index', ['search' => 'zack']));

        $response->assertOk()->assertSee('Zack Customer');
    }

    public function test_kpi_counts_manual_and_synced_records(): void
    {
        $this->registration(['name' => 'Manual Pending', 'payment_status' => 'pending']);
        $this->registration(['name' => 'Synced Unpaid', 'invoice_amount' => 500, 'total_paid' => 0]);

        $response = $this->actingAs($this->admin)->get(route('payment-alerts.index'));

        $response->assertOk()
            ->assertSee('Manual Pending')
            ->assertSee('Synced Unpaid');
    }

    public function test_admin_sees_all_pics_payment_records(): void
    {
        $this->registration([
            'name' => 'Zack Customer',
            'pic_utama' => 'Zack',
            'invoice_amount' => 500,
            'total_paid' => 0,
        ]);
        $this->registration([
            'name' => 'Aina Customer',
            'pic_in_house' => 'Aina Kamal',
            'invoice_amount' => 700,
            'total_paid' => 200,
        ]);
        $this->registration([
            'name' => 'Other Customer',
            'pic_utama' => 'Someone Else',
            'invoice_amount' => 900,
            'total_paid' => 900,
        ]);

        $response = $this->actingAs($this->admin)->get(route('payment-alerts.index'));

        $response->assertOk()
            ->assertSee('Zack Customer')
            ->assertSee('Aina Customer')
            ->assertSee('Other Customer');
    }

    public function test_pic_only_sees_own_records(): void
    {
        $this->registration([
            'name' => 'Zack Customer',
            'pic_utama' => 'Zack',
            'invoice_amount' => 500,
            'total_paid' => 0,
        ]);
        $this->registration([
            'name' => 'Aina Customer',
            'pic_in_house' => 'Aina Kamal',
            'invoice_amount' => 700,
            'total_paid' => 200,
        ]);
        $this->registration([
            'name' => 'Other Customer',
            'pic_utama' => 'Someone Else',
            'invoice_amount' => 900,
            'total_paid' => 0,
        ]);

        $response = $this->actingAs($this->picZack)->get(route('payment-alerts.index'));

        $response->assertOk()
            ->assertSee('Zack Customer')
            ->assertDontSee('Aina Customer')
            ->assertDontSee('Other Customer');
    }

    public function test_pic_matching_is_case_insensitive(): void
    {
        $this->registration([
            'name' => 'Aina Customer',
            'pic_in_house' => 'aina kamal',
            'invoice_amount' => 700,
            'total_paid' => 0,
        ]);

        // picAina has no pic_name set — falls back to their display name
        $response = $this->actingAs($this->picAina)->get(route('payment-alerts.index'));

        $response->assertOk()->assertSee('Aina Customer');
    }

    public function test_badge_counts_only_belum_bayar_and_partial(): void
    {
        $this->registration(['invoice_amount' => 500, 'total_paid' => 0]); // belum bayar
        $this->registration(['invoice_amount' => 500, 'total_paid' => 100]); // partial
        $this->registration(['invoice_amount' => 500, 'total_paid' => 500]); // paid
        $this->registration(['invoice_status' => 'cancelled', 'invoice_amount' => 500, 'total_paid' => 0]); // cancelled

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk();

        $this->assertSame(2, Registration::requiresPaymentFollowUp()->count());
        $this->assertSame(2, (clone Registration::query())->requiresPaymentFollowUp()->count());
    }

    public function test_badge_respects_pic_scope(): void
    {
        $this->registration([
            'pic_utama' => 'Zack',
            'invoice_amount' => 500,
            'total_paid' => 0,
        ]);
        $this->registration([
            'pic_utama' => 'Someone Else',
            'invoice_amount' => 500,
            'total_paid' => 0,
        ]);

        $this->assertSame(
            1,
            (clone Registration::query())->forPic($this->picZack->picFilterName())->requiresPaymentFollowUp()->count()
        );
    }

    public function test_payment_page_shows_kpi_totals(): void
    {
        $this->registration([
            'name' => 'Belum Bayar One',
            'invoice_amount' => 500,
            'total_paid' => 0,
        ]);
        $this->registration([
            'name' => 'Partial One',
            'invoice_amount' => 1000,
            'total_paid' => 400,
        ]);
        $this->registration([
            'name' => 'Paid One',
            'invoice_amount' => 800,
            'total_paid' => 800,
        ]);

        $response = $this->actingAs($this->admin)->get(route('payment-alerts.index'));

        $response->assertOk()
            ->assertSee('RM 1,100.00') // outstanding = 500 + 600
            ->assertSee('Belum Bayar')
            ->assertSee('Partial');
    }

    public function test_invoice_url_link_is_rendered(): void
    {
        $this->registration([
            'name' => 'Invoice Linked Customer',
            'invoice_amount' => 500,
            'total_paid' => 0,
            'invoice_url' => 'https://billing.example.com/invoice/12345',
        ]);

        $response = $this->actingAs($this->admin)->get(route('payment-alerts.index'));

        $response->assertOk()
            ->assertSee('href="https://billing.example.com/invoice/12345"', false);
    }

    public function test_status_filter_works(): void
    {
        $this->registration(['name' => 'Unpaid Customer', 'invoice_amount' => 500, 'total_paid' => 0]);
        $this->registration(['name' => 'Paid Customer', 'invoice_amount' => 500, 'total_paid' => 500]);

        $response = $this->actingAs($this->admin)
            ->get(route('payment-alerts.index', ['status' => 'paid']));

        $response->assertOk()
            ->assertSee('Paid Customer')
            ->assertDontSee('Unpaid Customer');
    }

    public function test_sync_imports_invoice_and_pic_fields(): void
    {
        config(['services.import.token' => 'test-token']);

        $payload = json_decode(
            file_get_contents(base_path('docs/samples/dropbox-excel-import.sample.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->withToken('test-token')
            ->postJson('/api/imports/dropbox-excel', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'completed');

        $ahmad = Registration::where('name', 'Ahmad Ali')->firstOrFail();
        $this->assertSame('INV-2026-0001', $ahmad->invoice_no);
        $this->assertSame('Zack', $ahmad->pic_utama);
        $this->assertSame('Aina', $ahmad->pic_in_house);
        $this->assertSame('belum_bayar', $ahmad->derived_payment_status);

        $siti = Registration::where('name', 'Siti Aminah')->firstOrFail();
        $this->assertSame('partial', $siti->derived_payment_status);
        $this->assertSame(1599.0, $siti->balance);
    }

    public function test_resync_updates_payment_without_duplicates(): void
    {
        config(['services.import.token' => 'test-token']);

        $payload = json_decode(
            file_get_contents(base_path('docs/samples/dropbox-excel-import.sample.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->withToken('test-token')->postJson('/api/imports/dropbox-excel', $payload)->assertOk();

        // Customer settles the full amount
        $payload['registrations'][0]['total_paid'] = 5198;

        $this->withToken('test-token')->postJson('/api/imports/dropbox-excel', $payload)->assertOk();

        $this->assertDatabaseCount('registrations', 2);

        $ahmad = Registration::where('name', 'Ahmad Ali')->firstOrFail();
        $this->assertSame('paid', $ahmad->derived_payment_status);
    }
}
