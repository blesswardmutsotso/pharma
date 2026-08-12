<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PickingAndBackorderVisibilityTest extends TestCase
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

    protected function createPartiallyBackorderedSalesOrder(): SalesOrder
    {
        $client = Client::create(['name' => 'Backorder Visibility Client']);

        Stock::factory()->create(['product_code' => 'BO-VIS-1', 'quantity' => 0]);
        StockBatch::create([
            'product_code' => 'BO-VIS-1',
            'batch_number' => 'BO-VIS-BATCH-1',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 4,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $this->post('/sales-orders', [
            'client_id' => $client->id,
            'currency' => 'USD',
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'BO-VIS-1',
                'product_description' => 'Backorder Visibility Product',
                'qty_ordered' => 10,
                'unit_price' => 5,
            ]],
        ]);
        $so = SalesOrder::latest('id')->firstOrFail();
        $this->post("/sales-orders/{$so->id}/confirm");
        $this->post("/sales-orders/{$so->id}/start-picking");

        return $so->fresh(['client', 'branch', 'items.batchAllocations.stockBatch']);
    }

    public function test_sales_order_pdf_explains_backordered_lines_instead_of_silently_omitting_them(): void
    {
        $this->actingAsAdmin();
        $so = $this->createPartiallyBackorderedSalesOrder();

        $html = view('pdf.sales-order', ['salesOrder' => $so, 'isDuplicate' => false])->render();

        $this->assertStringContainsString('BO-VIS-BATCH-1', $html);
        $this->assertStringContainsString('Backordered', $html);
        $this->assertStringContainsString('6 units', $html);
    }

    public function test_picking_list_explains_backordered_lines_instead_of_silently_omitting_them(): void
    {
        $this->actingAsAdmin();
        $so = $this->createPartiallyBackorderedSalesOrder();

        $response = $this->get("/sales-orders/{$so->id}/picking-list");

        $response->assertOk();
        $response->assertSeeText('BO-VIS-BATCH-1');
        $response->assertSeeText('Backordered');
        $response->assertSeeText('PICKING SLIP');
    }
}
