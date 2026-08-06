<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\ExchangeRate;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExchangeRateTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdmin(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
            'role' => User::ROLE_ADMIN,
        ]);
        $this->actingAs($user);

        return $user;
    }

    public function test_admin_can_set_an_exchange_rate(): void
    {
        $this->actingAsAdmin();

        $response = $this->post('/admin/settings/exchange-rates', [
            'currency_code' => 'ZWG',
            'rate_to_usd' => 30.5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('exchange_rates', [
            'currency_code' => 'ZWG',
            'rate_to_usd' => 30.5,
        ]);
    }

    public function test_settings_page_renders_and_shows_saved_rate(): void
    {
        $this->actingAsAdmin();
        $this->post('/admin/settings/exchange-rates', ['currency_code' => 'ZWG', 'rate_to_usd' => 25]);

        $response = $this->get('/admin/settings');
        $response->assertOk();
        $response->assertSeeText('25.000000');
    }

    public function test_toUsd_converts_using_the_configured_rate(): void
    {
        ExchangeRate::create(['currency_code' => 'ZWG', 'rate_to_usd' => 25]);

        $this->assertEqualsWithDelta(4.0, ExchangeRate::toUsd(100, 'ZWG'), 0.001);
        $this->assertEqualsWithDelta(100.0, ExchangeRate::toUsd(100, 'USD'), 0.001);
    }

    public function test_toUsd_passes_through_unchanged_when_no_rate_configured(): void
    {
        $this->assertEqualsWithDelta(100.0, ExchangeRate::toUsd(100, 'ZWG'), 0.001);
    }

    public function test_dashboard_and_analytics_convert_zwg_invoices_to_usd_before_summing(): void
    {
        ExchangeRate::create(['currency_code' => 'ZWG', 'rate_to_usd' => 20]);

        $this->actingAsAdmin();
        $client = Client::create(['name' => 'FX Client']);

        $usdOrder = SalesOrder::create([
            'so_number' => 'SO-FX-USD',
            'client_id' => $client->id,
            'currency' => 'USD',
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_INVOICED,
        ]);
        SalesInvoice::create([
            'invoice_number' => 'INV-FX-USD',
            'sales_order_id' => $usdOrder->id,
            'client_id' => $client->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => SalesInvoice::STATUS_UNPAID,
            'subtotal' => 100, 'tax_total' => 0, 'total' => 100,
        ]);

        $zwgOrder = SalesOrder::create([
            'so_number' => 'SO-FX-ZWG',
            'client_id' => $client->id,
            'currency' => 'ZWG',
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_INVOICED,
        ]);
        SalesInvoice::create([
            'invoice_number' => 'INV-FX-ZWG',
            'sales_order_id' => $zwgOrder->id,
            'client_id' => $client->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => SalesInvoice::STATUS_UNPAID,
            'subtotal' => 2000, 'tax_total' => 0, 'total' => 2000,
        ]);

        // 100 USD + (2000 ZWG / 20) = 100 + 100 = 200 USD outstanding — never the raw 2100.
        $dashboard = $this->get('/dashboard');
        $dashboard->assertOk();
        $dashboard->assertSeeText('200.00');
        $dashboard->assertDontSeeText('2,100.00');

        $analytics = $this->get('/analytics');
        $analytics->assertOk();
        $analytics->assertSeeText('200.00');
    }
}
