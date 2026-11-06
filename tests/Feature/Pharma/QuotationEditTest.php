<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationEditTest extends TestCase
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

    protected function makeQuotation(string $status = Quotation::STATUS_DRAFT): Quotation
    {
        $client = Client::create(['name' => 'Edit Test Client']);

        $quotation = Quotation::create([
            'quote_number' => Quotation::generateQuoteNumber(),
            'client_id' => $client->id,
            'currency' => 'USD',
            'quote_date' => now()->toDateString(),
            'status' => $status,
            'created_by' => auth()->id(),
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_code' => 'ORIG-1',
            'product_description' => 'Original Item',
            'qty' => 2,
            'unit_price' => 10,
            'discount' => 0,
            'line_total' => 20,
        ]);

        return $quotation;
    }

    public function test_a_draft_quotation_can_be_edited(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation();

        $this->get(route('quotations.edit', $quotation))->assertOk();

        $newClient = Client::create(['name' => 'New Client']);

        $this->put(route('quotations.update', $quotation), [
            'client_id' => $newClient->id,
            'currency' => 'USD',
            'quote_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'UPDATED-1',
                'product_description' => 'Updated Item',
                'qty' => 5,
                'unit_price' => 20,
            ]],
        ])->assertRedirect(route('quotations.show', $quotation));

        $quotation->refresh();
        $this->assertSame($newClient->id, $quotation->client_id);
        $this->assertSame(1, $quotation->items()->count());

        $item = $quotation->items()->first();
        $this->assertSame('UPDATED-1', $item->product_code);
        $this->assertEqualsWithDelta(100.0, (float) $item->line_total, 0.001);

        // The original line item is gone, not left dangling.
        $this->assertDatabaseMissing('quotation_items', ['product_code' => 'ORIG-1']);
    }

    public function test_a_converted_quotation_cannot_be_edited(): void
    {
        $this->actingAsAdmin();
        $quotation = $this->makeQuotation(Quotation::STATUS_CONVERTED);

        $salesOrder = SalesOrder::create([
            'so_number' => SalesOrder::generateSoNumber(),
            'client_id' => $quotation->client_id,
            'currency' => 'USD',
            'quotation_id' => $quotation->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ]);
        $quotation->update(['converted_sales_order_id' => $salesOrder->id]);

        $this->get(route('quotations.edit', $quotation))
            ->assertRedirect(route('quotations.show', $quotation));

        $this->put(route('quotations.update', $quotation), [
            'client_id' => $quotation->client_id,
            'currency' => 'USD',
            'quote_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'SHOULD-NOT-APPLY',
                'product_description' => 'Blocked',
                'qty' => 1,
                'unit_price' => 1,
            ]],
        ])->assertRedirect(route('quotations.show', $quotation));

        $this->assertDatabaseHas('quotation_items', ['product_code' => 'ORIG-1']);
        $this->assertDatabaseMissing('quotation_items', ['product_code' => 'SHOULD-NOT-APPLY']);
    }
}
