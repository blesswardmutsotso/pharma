<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Quotations must support lines for things that aren't in the product
 * catalogue at all (services, delivery fees, one-off items) — unlike GRN or
 * Sales Orders, a quotation never commits real stock, so there's no reason
 * to require the line to match an existing Stock record.
 */
class QuotationCustomItemTest extends TestCase
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

    public function test_quotation_can_include_a_custom_item_not_in_the_catalogue(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Custom Item Client']);

        $this->post('/quotations', [
            'client_id' => $client->id,
            'currency' => 'USD',
            'quote_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'CUSTOM-1',
                'product_description' => 'On-site installation service',
                'qty' => 1,
                'unit_price' => 250,
            ]],
        ])->assertRedirect();

        $quotation = Quotation::where('client_id', $client->id)->firstOrFail();
        $item = QuotationItem::where('quotation_id', $quotation->id)->firstOrFail();

        $this->assertSame('CUSTOM-1', $item->product_code);
        $this->assertSame('On-site installation service', $item->product_description);
        $this->assertEqualsWithDelta(250.0, (float) $item->line_total, 0.001);

        // No Stock record for this code exists anywhere — confirms nothing
        // silently requires one.
        $this->assertDatabaseMissing('stocks', ['product_code' => 'CUSTOM-1']);
    }

    public function test_quotation_can_mix_catalogue_and_custom_items(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Mixed Items Client']);

        \App\Models\Stock::factory()->create(['product_code' => 'REAL-PRD', 'selling_price' => 5]);

        $this->post('/quotations', [
            'client_id' => $client->id,
            'currency' => 'USD',
            'quote_date' => now()->toDateString(),
            'items' => [
                [
                    'product_code' => 'REAL-PRD',
                    'product_description' => 'Real Catalogue Product',
                    'qty' => 2,
                    'unit_price' => 5,
                ],
                [
                    'product_code' => 'CUSTOM-2',
                    'product_description' => 'Delivery Fee',
                    'qty' => 1,
                    'unit_price' => 15,
                ],
            ],
        ])->assertRedirect();

        $quotation = Quotation::where('client_id', $client->id)->firstOrFail();
        $this->assertSame(2, $quotation->items()->count());
        $this->assertEqualsWithDelta(25.0, (float) $quotation->items->sum('line_total'), 0.001);
    }
}
