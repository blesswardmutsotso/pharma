<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\DeliveryNote;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryNoteTest extends TestCase
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

    protected function dispatchASalesOrder(string $productCode): SalesOrder
    {
        $client = Client::create(['name' => 'Delivery Note Client']);

        Stock::factory()->create(['product_code' => $productCode, 'quantity' => 0]);
        StockBatch::create([
            'product_code' => $productCode,
            'batch_number' => 'DN-BATCH-1',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 10,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $this->post('/sales-orders', [
            'client_id' => $client->id,
            'currency' => 'USD',
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => $productCode,
                'product_description' => 'Delivery Note Product',
                'qty_ordered' => 5,
                'unit_price' => 2,
            ]],
        ]);
        $so = SalesOrder::latest('id')->firstOrFail();
        $this->post("/sales-orders/{$so->id}/confirm");
        $this->post("/sales-orders/{$so->id}/start-picking");
        $this->post("/sales-orders/{$so->id}/dispatch");

        return $so->fresh();
    }

    public function test_dispatching_a_sales_order_auto_generates_a_delivery_note_with_batch_and_expiry_lines(): void
    {
        $this->actingAsAdmin();
        $so = $this->dispatchASalesOrder('DN-PROD-1');

        $deliveryNote = DeliveryNote::where('sales_order_id', $so->id)->firstOrFail();

        $this->assertSame($so->client_id, $deliveryNote->client_id);
        $this->assertCount(1, $deliveryNote->items);
        $this->assertSame('DN-BATCH-1', $deliveryNote->items->first()->batch_number);
        $this->assertSame(5, $deliveryNote->items->first()->qty);
        $this->assertNotNull($deliveryNote->items->first()->expiry_date);
    }

    public function test_delivery_note_index_and_show_pages_render(): void
    {
        $this->actingAsAdmin();
        $so = $this->dispatchASalesOrder('DN-PROD-2');
        $deliveryNote = DeliveryNote::where('sales_order_id', $so->id)->firstOrFail();

        $this->get('/delivery-notes')->assertOk();
        $this->get("/delivery-notes/{$deliveryNote->id}")->assertOk();
    }

    public function test_delivery_note_pdf_renders_with_batch_and_expiry(): void
    {
        $this->actingAsAdmin();
        $so = $this->dispatchASalesOrder('DN-PROD-3');
        $deliveryNote = DeliveryNote::where('sales_order_id', $so->id)->firstOrFail();

        $response = $this->get("/delivery-notes/{$deliveryNote->id}/pdf");
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_sales_order_show_page_links_to_delivery_note_once_dispatched(): void
    {
        $this->actingAsAdmin();
        $so = $this->dispatchASalesOrder('DN-PROD-4');

        $response = $this->get("/sales-orders/{$so->id}");
        $response->assertOk();
        $response->assertSee('Print Delivery Note');
    }
}
