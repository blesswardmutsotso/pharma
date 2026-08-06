<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationCurrencyTest extends TestCase
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

    public function test_quotation_can_be_created_in_zwg(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'ZWG Quote Client']);

        $this->post('/quotations', [
            'client_id' => $client->id,
            'currency' => 'ZWG',
            'quote_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'QCUR-1',
                'product_description' => 'Test Product',
                'qty' => 1,
                'unit_price' => 10,
            ]],
        ])->assertRedirect();

        $quotation = Quotation::where('client_id', $client->id)->firstOrFail();
        $this->assertSame('ZWG', $quotation->currency);
    }

    public function test_quotation_defaults_to_usd_when_not_specified_via_model(): void
    {
        $client = Client::create(['name' => 'Default Currency Client']);
        $quotation = Quotation::create([
            'quote_number' => 'QUO-DEFAULT-1',
            'client_id' => $client->id,
            'quote_date' => now()->toDateString(),
            'status' => Quotation::STATUS_DRAFT,
        ]);

        $this->assertSame('USD', $quotation->fresh()->currency);
    }

    public function test_quotation_show_and_pdf_display_its_currency(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Currency Display Client']);
        $quotation = Quotation::create([
            'quote_number' => 'QUO-DISPLAY-1',
            'client_id' => $client->id,
            'currency' => 'ZWG',
            'quote_date' => now()->toDateString(),
            'status' => Quotation::STATUS_DRAFT,
        ]);

        $showResponse = $this->get("/quotations/{$quotation->id}");
        $showResponse->assertOk();
        $showResponse->assertSeeText('ZWG');

        $pdfHtml = view('pdf.quotation', ['quotation' => $quotation->load('client', 'items'), 'isDuplicate' => false])->render();
        $this->assertStringContainsString('ZWG', $pdfHtml);
    }

    public function test_converting_a_quotation_carries_its_currency_to_the_new_sales_order(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Convert Currency Client']);
        $quotation = Quotation::create([
            'quote_number' => 'QUO-CONVERT-1',
            'client_id' => $client->id,
            'currency' => 'ZWG',
            'quote_date' => now()->toDateString(),
            'status' => Quotation::STATUS_DRAFT,
        ]);
        \App\Models\QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_code' => 'QCONV-1',
            'product_description' => 'Test Product',
            'qty' => 2,
            'unit_price' => 5,
            'discount' => 0,
            'line_total' => 10,
        ]);

        $this->post("/quotations/{$quotation->id}/convert")->assertRedirect();

        $salesOrder = SalesOrder::where('quotation_id', $quotation->id)->firstOrFail();
        $this->assertSame('ZWG', $salesOrder->currency);
    }
}
