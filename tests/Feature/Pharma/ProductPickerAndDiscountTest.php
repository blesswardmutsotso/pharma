<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPickerAndDiscountTest extends TestCase
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

    public function test_product_search_returns_more_than_the_old_fifteen_result_cap(): void
    {
        $this->actingAsAdmin();

        for ($i = 1; $i <= 20; $i++) {
            Stock::factory()->create([
                'product_code' => "PICKER-{$i}",
                'product_description' => "Picker Test Product {$i}",
            ]);
        }

        $response = $this->getJson('/products/search?q=Picker Test Product');

        $response->assertOk();
        $this->assertGreaterThan(15, count($response->json()));
    }

    public function test_product_search_includes_next_fefo_batch_number_and_expiry(): void
    {
        $this->actingAsAdmin();
        Stock::factory()->create(['product_code' => 'PICKER-BATCH-1', 'product_description' => 'Picker Batch Product']);

        StockBatch::create([
            'product_code' => 'PICKER-BATCH-1',
            'batch_number' => 'FAR-BATCH',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 5,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);
        StockBatch::create([
            'product_code' => 'PICKER-BATCH-1',
            'batch_number' => 'SOON-BATCH',
            'expiry_date' => now()->addDays(10),
            'qty_on_hand' => 5,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $response = $this->getJson('/products/search?q=Picker Batch Product');

        $response->assertOk();
        $product = collect($response->json())->firstWhere('product_code', 'PICKER-BATCH-1');

        $this->assertSame('SOON-BATCH', $product['batch_number']);
    }

    public function test_sales_order_line_discount_is_saved_and_subtracted_from_line_total(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Discount Test Client']);

        $this->post('/sales-orders', [
            'client_id' => $client->id,
            'currency' => 'USD',
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'DISC-1',
                'product_description' => 'Discount Test Product',
                'qty_ordered' => 10,
                'unit_price' => 5,
                'discount' => 3,
            ]],
        ]);

        $so = SalesOrder::where('client_id', $client->id)->firstOrFail();
        $item = SalesOrderItem::where('sales_order_id', $so->id)->firstOrFail();

        $this->assertEqualsWithDelta(3.0, (float) $item->discount, 0.001);
        $this->assertEqualsWithDelta(47.0, (float) $item->line_total, 0.001);
    }

    public function test_sales_order_line_without_discount_defaults_to_zero(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'No Discount Client']);

        $this->post('/sales-orders', [
            'client_id' => $client->id,
            'currency' => 'USD',
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'DISC-2',
                'product_description' => 'No Discount Product',
                'qty_ordered' => 4,
                'unit_price' => 2,
            ]],
        ]);

        $so = SalesOrder::where('client_id', $client->id)->firstOrFail();
        $item = SalesOrderItem::where('sales_order_id', $so->id)->firstOrFail();

        $this->assertEqualsWithDelta(0.0, (float) $item->discount, 0.001);
        $this->assertEqualsWithDelta(8.0, (float) $item->line_total, 0.001);
    }
}
