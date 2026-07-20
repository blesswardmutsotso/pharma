<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdmin(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
            'user_type' => 1,
            'role' => User::ROLE_ADMIN,
        ]);
        $this->actingAs($user);

        return $user;
    }

    public function test_key_pages_render_for_an_admin_user(): void
    {
        $this->actingAsAdmin();

        $pages = [
            '/dashboard',
            '/products',
            '/products/create',
            '/suppliers',
            '/suppliers/create',
            '/purchase-orders',
            '/purchase-orders/create',
            '/goods-received-notes',
            '/goods-received-notes/create',
            '/quotations',
            '/quotations/create',
            '/sales-orders',
            '/sales-orders/create',
            '/sales-invoices',
            '/stock/transfers',
            '/account/settings',
            '/analytics',
            '/admin/branches',
            '/reports',
            '/reports/current-stock',
            '/reports/stock-movement',
            '/reports/expiry-alert',
            '/reports/low-stock',
            '/reports/stock-valuation',
            '/reports/sales-summary',
            '/reports/sales-order-status',
            '/reports/purchase-order-report',
            '/reports/supplier-performance',
            '/reports/debtors-ageing',
            '/reports/revenue-report',
            '/reports/batch-traceability',
            '/reports/batch-recall',
        ];

        foreach ($pages as $page) {
            $response = $this->get($page);
            $this->assertTrue(
                $response->status() < 500,
                "Page {$page} returned a server error ({$response->status()})"
            );
        }
    }

    public function test_login_page_renders_for_guest(): void
    {
        $response = $this->get('/');
        $response->assertOk();
    }

    /**
     * Drives a product all the way from creation through PO/GRN receiving,
     * quotation, sales order confirm/pick/dispatch (auto-invoice), payment
     * and credit note — then hits every detail/show page along the way.
     * This is the only test that actually renders the rewritten show views
     * with real batch allocations, payments, and credit notes loaded.
     */
    public function test_full_pipeline_detail_pages_render(): void
    {
        $this->actingAsAdmin();

        $supplier = Supplier::factory()->create(['status' => 'active']);
        $client = Client::create(['name' => 'Smoke Test Pharmacy']);

        Stock::factory()->create(['product_code' => 'SMOKE-1', 'quantity' => 0]);
        $this->get('/products')->assertOk();
        $productId = Stock::where('product_code', 'SMOKE-1')->value('id');
        $this->get("/products/{$productId}")->assertOk();
        $this->get("/products/{$productId}/edit")->assertOk();

        $this->get("/suppliers/{$supplier->id}")->assertOk();

        $poResponse = $this->post('/purchase-orders', [
            'po_number' => 'PO-SMOKE-1',
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'status' => 'draft',
            'items' => [[
                'product_code' => 'SMOKE-1',
                'product_description' => 'Smoke Test Product',
                'qty_ordered' => 20,
                'unit_cost' => 1.00,
            ]],
        ]);
        $poResponse->assertRedirect();
        $po = \App\Models\PurchaseOrder::where('po_number', 'PO-SMOKE-1')->firstOrFail();
        $this->get("/purchase-orders/{$po->id}")->assertOk();
        $this->post("/purchase-orders/{$po->id}/submit");
        $this->post("/purchase-orders/{$po->id}/approve");
        $this->get("/purchase-orders/{$po->id}")->assertOk();

        $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-SMOKE-1',
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'received_date' => now()->toDateString(),
            'status' => 'received',
            'items' => [[
                'product_code' => 'SMOKE-1',
                'product_description' => 'Smoke Test Product',
                'qty_received' => 20,
                'unit_cost' => 1.00,
                'batch_number' => 'SMOKE-BATCH-1',
                'expiry_date' => now()->addYear()->toDateString(),
                'status' => 'accepted',
            ]],
        ]);
        $grn = \App\Models\GoodsReceivedNote::where('grn_number', 'GRN-SMOKE-1')->firstOrFail();
        $this->get("/goods-received-notes/{$grn->id}")->assertOk();

        $quotation = Quotation::create([
            'quote_number' => 'QUO-SMOKE-1',
            'client_id' => $client->id,
            'quote_date' => now()->toDateString(),
            'status' => Quotation::STATUS_DRAFT,
        ]);
        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_code' => 'SMOKE-1',
            'product_description' => 'Smoke Test Product',
            'qty' => 5,
            'unit_price' => 3.00,
            'discount' => 0,
            'line_total' => 15.00,
        ]);
        $this->get("/quotations/{$quotation->id}")->assertOk();
        $this->post("/quotations/{$quotation->id}/convert");

        $this->post('/sales-orders', [
            'so_number' => 'SO-SMOKE-1',
            'client_id' => $client->id,
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'SMOKE-1',
                'product_description' => 'Smoke Test Product',
                'qty_ordered' => 10,
                'unit_price' => 3.00,
            ]],
        ]);
        $so = SalesOrder::where('so_number', 'SO-SMOKE-1')->firstOrFail();
        $this->get("/sales-orders/{$so->id}")->assertOk();
        $this->post("/sales-orders/{$so->id}/confirm");
        $this->get("/sales-orders/{$so->id}")->assertOk();
        $this->post("/sales-orders/{$so->id}/start-picking");
        $this->get("/sales-orders/{$so->id}/picking-list")->assertOk();
        $this->post("/sales-orders/{$so->id}/dispatch");
        $this->get("/sales-orders/{$so->id}")->assertOk();

        $invoice = $so->fresh()->invoice;
        $this->assertNotNull($invoice);
        $this->get("/sales-invoices/{$invoice->id}")->assertOk();
        $this->get('/sales-invoices')->assertOk();

        $this->post("/clients/{$client->id}/payments", [
            'amount' => 10.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'eft',
            'allocations' => [['sales_invoice_id' => $invoice->id, 'amount' => 10.00]],
        ]);
        $this->get("/sales-invoices/{$invoice->id}")->assertOk();

        $this->post("/sales-invoices/{$invoice->id}/credit-notes", [
            'amount' => 5.00,
            'reason' => 'Smoke test adjustment',
        ]);
        $this->get("/sales-invoices/{$invoice->id}")->assertOk();

        $this->get("/clients/{$client->id}/statement")->assertOk();

        $this->post("/sales-orders/{$so->id}/return", [
            'sales_order_item_id' => $so->items()->first()->id,
            'qty' => 2,
            'reason' => 'Smoke test return',
        ]);
        $this->get("/sales-orders/{$so->id}")->assertOk();
    }
}
