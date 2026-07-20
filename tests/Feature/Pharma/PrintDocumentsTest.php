<?php

namespace Tests\Feature\Pharma;

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

class PrintDocumentsTest extends TestCase
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

    public function test_quotation_pdf_renders_and_tracks_duplicate_printing(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Print Test Client']);

        $quotation = Quotation::create([
            'quote_number' => 'QUO-PDF-1',
            'client_id' => $client->id,
            'quote_date' => now()->toDateString(),
            'status' => Quotation::STATUS_DRAFT,
        ]);
        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_code' => 'PDF-1',
            'product_description' => 'Test Product',
            'qty' => 2,
            'unit_price' => 5,
            'discount' => 0,
            'line_total' => 10,
        ]);

        $first = $this->get("/quotations/{$quotation->id}/pdf");
        $first->assertOk();
        $first->assertHeader('content-type', 'application/pdf');
        $this->assertSame(1, $quotation->fresh()->print_count);

        // Second view of the same document should be tracked as a duplicate print.
        $second = $this->get("/quotations/{$quotation->id}/pdf");
        $second->assertOk();
        $this->assertSame(2, $quotation->fresh()->print_count);
    }

    public function test_grn_pdf_renders(): void
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create(['status' => 'active']);

        $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-PDF-1',
            'purchase_order_id' => null,
            'supplier_id' => $supplier->id,
            'received_date' => now()->toDateString(),
            'status' => 'received',
            'items' => [[
                'product_code' => 'PDF-2',
                'product_description' => 'Test Product',
                'qty_received' => 5,
                'unit_cost' => 1.5,
                'batch_number' => 'PDF-BATCH-1',
                'expiry_date' => now()->addYear()->toDateString(),
                'status' => 'accepted',
            ]],
        ]);

        $grn = \App\Models\GoodsReceivedNote::where('grn_number', 'GRN-PDF-1')->firstOrFail();

        $response = $this->get("/goods-received-notes/{$grn->id}/pdf");
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_invoice_pdf_renders_with_qr_code(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Invoice PDF Client']);

        Stock::factory()->create(['product_code' => 'PDF-3', 'quantity' => 0]);
        StockBatch::create([
            'product_code' => 'PDF-3',
            'batch_number' => 'PDF-BATCH-2',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 10,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $this->post('/sales-orders', [
            'so_number' => 'SO-PDF-1',
            'client_id' => $client->id,
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'PDF-3',
                'product_description' => 'Test Product',
                'qty_ordered' => 5,
                'unit_price' => 4,
            ]],
        ]);
        $so = SalesOrder::where('so_number', 'SO-PDF-1')->firstOrFail();
        $this->post("/sales-orders/{$so->id}/confirm");
        $this->post("/sales-orders/{$so->id}/start-picking");
        $this->post("/sales-orders/{$so->id}/dispatch");

        $invoice = $so->fresh()->invoice;

        $response = $this->get("/sales-invoices/{$invoice->id}/pdf");
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
