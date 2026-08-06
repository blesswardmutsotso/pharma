<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTotalsCompletenessTest extends TestCase
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

    public function test_quotation_index_shows_a_total_column(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Totals Client']);

        $this->post('/quotations', [
            'client_id' => $client->id,
            'currency' => 'USD',
            'quote_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'TOT-1',
                'product_description' => 'Totals Product',
                'qty' => 2,
                'unit_price' => 10,
                'discount' => 0,
            ]],
        ]);

        $response = $this->get('/quotations');

        $response->assertOk();
        $response->assertSee('20.00');
    }

    public function test_sales_order_index_shows_a_total_column(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'SO Totals Client']);

        $this->post('/sales-orders', [
            'client_id' => $client->id,
            'currency' => 'USD',
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'TOT-2',
                'product_description' => 'Totals Product',
                'qty_ordered' => 3,
                'unit_price' => 5,
            ]],
        ]);

        $response = $this->get('/sales-orders');

        $response->assertOk();
        $response->assertSee('15.00');
    }

    public function test_purchase_order_index_shows_a_total_column(): void
    {
        $this->actingAsAdmin();
        $supplier = \App\Models\Supplier::factory()->create(['status' => 'active']);

        $this->post('/purchase-orders', [
            'po_number' => 'PO-TOT-1',
            'supplier_id' => $supplier->id,
            'currency' => 'USD',
            'order_date' => now()->toDateString(),
            'status' => 'draft',
            'items' => [[
                'product_code' => 'TOT-3',
                'product_description' => 'Totals Product',
                'qty_ordered' => 4,
                'unit_cost' => 2.5,
            ]],
        ]);

        $response = $this->get('/purchase-orders');

        $response->assertOk();
        $response->assertSee('10.00');
    }

    public function test_stock_adjustment_show_and_index_display_expiry_and_net_value_impact(): void
    {
        $this->actingAsAdmin();

        $stock = Stock::factory()->create(['product_code' => 'ADJ-TOT-1', 'quantity' => 10]);
        $batch = StockBatch::create([
            'product_code' => 'ADJ-TOT-1',
            'batch_number' => 'ADJ-BATCH-1',
            'expiry_date' => now()->addMonths(3),
            'qty_on_hand' => 10,
            'unit_cost' => 2,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $adjustment = StockAdjustment::create([
            'adjustment_no' => 'ADJ-TOT-0001',
            'type' => StockAdjustment::TYPE_STOCK_TAKE,
            'status' => StockAdjustment::STATUS_SUBMITTED,
            'requested_by' => auth()->id(),
        ]);
        StockAdjustmentItem::create([
            'stock_adjustment_id' => $adjustment->id,
            'product_code' => 'ADJ-TOT-1',
            'product_description' => $stock->product_description,
            'stock_batch_id' => $batch->id,
            'batch_number' => $batch->batch_number,
            'qty_system' => 10,
            'qty_counted' => 8,
            'qty_variance' => -2,
            'unit_cost' => 2,
        ]);

        $showResponse = $this->get("/stock-adjustments/{$adjustment->id}");
        $showResponse->assertOk();
        $showResponse->assertSee($batch->expiry_date->format('Y-m-d'));
        $showResponse->assertSee('-4.00');

        $indexResponse = $this->get('/stock-adjustments');
        $indexResponse->assertOk();
        $indexResponse->assertSee('-4.00');
    }
}
