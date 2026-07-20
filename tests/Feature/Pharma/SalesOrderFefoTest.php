<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderFefoTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAuthenticatedUser(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
            'user_type' => 3,
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($user);

        return $user;
    }

    protected function createClient(): Client
    {
        return Client::create(['name' => 'Harare Central Clinic']);
    }

    protected function createSalesOrder(Client $client, string $productCode, int $qty): SalesOrder
    {
        $this->post('/sales-orders', [
            'so_number' => 'SO-TEST-0001',
            'client_id' => $client->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_code' => $productCode,
                    'product_description' => 'Test Product',
                    'qty_ordered' => $qty,
                    'unit_price' => 5.00,
                ],
            ],
        ]);

        return SalesOrder::where('so_number', 'SO-TEST-0001')->firstOrFail();
    }

    public function test_confirming_a_sales_order_allocates_stock_fefo_across_batches(): void
    {
        $this->actingAsAuthenticatedUser();
        $client = $this->createClient();

        Stock::factory()->create(['product_code' => 'PRD-SO-1', 'quantity' => 0]);

        $oldBatch = StockBatch::create([
            'product_code' => 'PRD-SO-1',
            'batch_number' => 'OLD',
            'expiry_date' => now()->addMonths(2),
            'qty_on_hand' => 30,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);
        $newBatch = StockBatch::create([
            'product_code' => 'PRD-SO-1',
            'batch_number' => 'NEW',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 30,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $so = $this->createSalesOrder($client, 'PRD-SO-1', 40);

        $this->post("/sales-orders/{$so->id}/confirm")->assertRedirect();

        $so->refresh();
        $this->assertSame(SalesOrder::STATUS_CONFIRMED, $so->status);

        $item = $so->items()->first();
        $this->assertSame(40, $item->qty_allocated);

        $oldBatch->refresh();
        $newBatch->refresh();
        // FEFO: the older (sooner-expiring) batch is drained first (30), then 10 from the new batch.
        $this->assertSame(30, $oldBatch->qty_reserved);
        $this->assertSame(10, $newBatch->qty_reserved);
    }

    public function test_confirming_a_sales_order_fails_when_stock_is_insufficient(): void
    {
        $this->actingAsAuthenticatedUser();
        $client = $this->createClient();

        Stock::factory()->create(['product_code' => 'PRD-SO-2', 'quantity' => 0]);
        StockBatch::create([
            'product_code' => 'PRD-SO-2',
            'batch_number' => 'ONLY',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 5,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $so = $this->createSalesOrder($client, 'PRD-SO-2', 10);

        $this->post("/sales-orders/{$so->id}/confirm")->assertRedirect();

        $this->assertSame(SalesOrder::STATUS_DRAFT, $so->fresh()->status);
        $this->assertSame(0, $so->items()->first()->qty_allocated);
    }

    public function test_confirming_a_sales_order_is_blocked_when_it_would_exceed_the_clients_credit_limit(): void
    {
        $this->actingAsAuthenticatedUser();
        $client = \App\Models\Client::create(['name' => 'Credit Limited Client', 'credit_limit' => 100]);

        // Existing unpaid invoice already uses up most of the limit.
        $existingSo = SalesOrder::create([
            'so_number' => 'SO-CREDIT-EXISTING',
            'client_id' => $client->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_INVOICED,
        ]);
        \App\Models\SalesInvoice::create([
            'invoice_number' => 'INV-CREDIT-EXISTING',
            'sales_order_id' => $existingSo->id,
            'client_id' => $client->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => \App\Models\SalesInvoice::STATUS_UNPAID,
            'subtotal' => 80, 'tax_total' => 0, 'total' => 80,
        ]);

        Stock::factory()->create(['product_code' => 'PRD-SO-CREDIT', 'quantity' => 0]);
        StockBatch::create([
            'product_code' => 'PRD-SO-CREDIT',
            'batch_number' => 'CREDIT-BATCH',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 50,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        // New order worth 30 would push total exposure to 110, over the 100 limit.
        $this->post('/sales-orders', [
            'so_number' => 'SO-CREDIT-NEW',
            'client_id' => $client->id,
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'PRD-SO-CREDIT',
                'product_description' => 'Test Product',
                'qty_ordered' => 10,
                'unit_price' => 3,
            ]],
        ]);
        $so = SalesOrder::where('so_number', 'SO-CREDIT-NEW')->firstOrFail();

        $response = $this->post("/sales-orders/{$so->id}/confirm");

        $response->assertRedirect();
        $this->assertSame(SalesOrder::STATUS_DRAFT, $so->fresh()->status);
        $this->assertStringContainsString('credit limit', session('error'));
    }

    public function test_dispatch_reduces_batch_and_aggregate_stock_and_logs_sale(): void
    {
        $this->actingAsAuthenticatedUser();
        $client = $this->createClient();

        $product = Stock::factory()->create(['product_code' => 'PRD-SO-3', 'quantity' => 0]);
        StockBatch::create([
            'product_code' => 'PRD-SO-3',
            'batch_number' => 'B1',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 20,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);
        $product->syncQuantityFromBatches();

        $so = $this->createSalesOrder($client, 'PRD-SO-3', 15);
        $this->post("/sales-orders/{$so->id}/confirm");
        $this->post("/sales-orders/{$so->id}/start-picking");
        $this->post("/sales-orders/{$so->id}/dispatch")->assertRedirect();

        // Dispatch auto-generates the invoice (BRD FR-INV-001), advancing status straight to Invoiced.
        $this->assertSame(SalesOrder::STATUS_INVOICED, $so->fresh()->status);
        $this->assertSame(5, $product->fresh()->quantity);

        $this->assertDatabaseHas('stock_audit_logs', [
            'action' => 'SALE',
            'product_code' => 'PRD-SO-3',
        ]);
    }

    public function test_cancelling_a_confirmed_sales_order_releases_reserved_stock(): void
    {
        $this->actingAsAuthenticatedUser();
        $client = $this->createClient();

        Stock::factory()->create(['product_code' => 'PRD-SO-4', 'quantity' => 0]);
        $batch = StockBatch::create([
            'product_code' => 'PRD-SO-4',
            'batch_number' => 'B1',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 20,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $so = $this->createSalesOrder($client, 'PRD-SO-4', 12);
        $this->post("/sales-orders/{$so->id}/confirm");

        $batch->refresh();
        $this->assertSame(12, $batch->qty_reserved);

        $this->post("/sales-orders/{$so->id}/cancel")->assertRedirect();

        $this->assertSame(SalesOrder::STATUS_CANCELLED, $so->fresh()->status);
        $this->assertSame(0, $batch->fresh()->qty_reserved);
        $this->assertSame(0, $so->items()->first()->qty_allocated);
    }

    public function test_return_after_dispatch_creates_quarantined_batch(): void
    {
        $this->actingAsAuthenticatedUser();
        $client = $this->createClient();

        $product = Stock::factory()->create(['product_code' => 'PRD-SO-5', 'quantity' => 0]);
        StockBatch::create([
            'product_code' => 'PRD-SO-5',
            'batch_number' => 'B1',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 20,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $so = $this->createSalesOrder($client, 'PRD-SO-5', 10);
        $this->post("/sales-orders/{$so->id}/confirm");
        $this->post("/sales-orders/{$so->id}/start-picking");
        $this->post("/sales-orders/{$so->id}/dispatch");

        $item = $so->items()->first();

        $this->post("/sales-orders/{$so->id}/return", [
            'sales_order_item_id' => $item->id,
            'qty' => 3,
            'reason' => 'Damaged packaging',
        ])->assertRedirect();

        $this->assertDatabaseHas('stock_batches', [
            'product_code' => 'PRD-SO-5',
            'status' => StockBatch::STATUS_QUARANTINE,
            'qty_on_hand' => 3,
        ]);

        // Quarantined returns must not count as sellable stock.
        $this->assertSame(10, $product->fresh()->quantity);
    }

    public function test_quotation_converts_to_draft_sales_order_with_matching_items(): void
    {
        $this->actingAsAuthenticatedUser();
        $client = $this->createClient();

        $quotation = Quotation::create([
            'quote_number' => 'QUO-TEST-0001',
            'client_id' => $client->id,
            'quote_date' => now()->toDateString(),
            'status' => Quotation::STATUS_DRAFT,
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_code' => 'PRD-SO-6',
            'product_description' => 'Test Product',
            'qty' => 8,
            'unit_price' => 4.00,
            'discount' => 0,
            'line_total' => 32.00,
        ]);

        $this->post("/quotations/{$quotation->id}/convert")->assertRedirect();

        $quotation->refresh();
        $this->assertSame(Quotation::STATUS_CONVERTED, $quotation->status);
        $this->assertNotNull($quotation->converted_sales_order_id);

        $so = SalesOrder::findOrFail($quotation->converted_sales_order_id);
        $this->assertSame(SalesOrder::STATUS_DRAFT, $so->status);
        $this->assertSame('PRD-SO-6', $so->items()->first()->product_code);
        $this->assertSame(8, $so->items()->first()->qty_ordered);
    }
}
