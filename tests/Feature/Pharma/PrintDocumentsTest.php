<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesInvoice;
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
        Stock::factory()->create(['product_code' => 'PDF-2']);

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

    public function test_sales_order_pdf_renders_and_tracks_duplicate_printing(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'SO PDF Client']);

        $this->post('/sales-orders', [
            'client_id' => $client->id,
            'currency' => 'USD',
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'SO-PDF-ITEM',
                'product_description' => 'Test Product',
                'qty_ordered' => 3,
                'unit_price' => 6,
            ]],
        ]);
        $so = SalesOrder::latest('id')->firstOrFail();

        $first = $this->get("/sales-orders/{$so->id}/pdf");
        $first->assertOk();
        $first->assertHeader('content-type', 'application/pdf');
        $this->assertSame(1, $so->fresh()->print_count);

        $second = $this->get("/sales-orders/{$so->id}/pdf");
        $second->assertOk();
        $this->assertSame(2, $so->fresh()->print_count);
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
            'client_id' => $client->id,
            'currency' => 'USD',
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'PDF-3',
                'product_description' => 'Test Product',
                'qty_ordered' => 5,
                'unit_price' => 4,
            ]],
        ]);
        $so = SalesOrder::latest('id')->firstOrFail();
        $this->post("/sales-orders/{$so->id}/confirm");
        $this->post("/sales-orders/{$so->id}/start-picking");
        $this->post("/sales-orders/{$so->id}/dispatch");

        $invoice = $so->fresh()->invoice;

        $response = $this->get("/sales-invoices/{$invoice->id}/pdf");
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_invoice_pdf_includes_contact_details_and_zig_bank_account(): void
    {
        config([
            'company.phone_sales' => '+263771234567',
            'company.email_sales' => 'sales@quvapharmaceuticals.co.zw',
            'company.bank_name' => 'Crown Bank Limited',
            'company.bank_branch' => 'Africa Unity Square',
            'company.bank_branch_code' => '701',
            'company.bank_swift_code' => 'SCBLZWHXXX',
            'company.bank_address' => '68 Nelson Mandela Avenue, Harare, Zimbabwe',
            'company.bank_account_name' => 'Quva Pharmaceuticals Private Limited',
            'company.bank_account_number' => '4167859920000',
            'company.zig_bank_account_number' => '0167859920000',
        ]);

        $this->actingAsAdmin();
        $client = Client::create(['name' => 'ZiG PDF Client']);
        $so = SalesOrder::create([
            'so_number' => 'SO-ZIG-PDF',
            'client_id' => $client->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_INVOICED,
        ]);
        $invoice = SalesInvoice::create([
            'invoice_number' => 'INV-ZIG-PDF',
            'sales_order_id' => $so->id,
            'client_id' => $client->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => SalesInvoice::STATUS_UNPAID,
            'subtotal' => 10, 'tax_total' => 0, 'total' => 10,
        ]);

        $html = view('pdf.invoice', [
            'invoice' => $invoice,
            'isDuplicate' => false,
            'qrImage' => 'data:image/svg+xml;base64,',
        ])->render();

        $this->assertStringContainsString('+263771234567', $html);
        $this->assertStringContainsString('sales@quvapharmaceuticals.co.zw', $html);
        $this->assertStringContainsString('Crown Bank Limited', $html);
        $this->assertStringContainsString('Africa Unity Square', $html);
        $this->assertStringContainsString('SCBLZWHXXX', $html);
        $this->assertStringContainsString('68 Nelson Mandela Avenue', $html);
        $this->assertStringContainsString('4167859920000', $html);
        $this->assertStringContainsString('0167859920000', $html);
        $this->assertStringContainsString('ZWG', $html);
    }

    public function test_sales_order_pdf_shows_batch_allocation_after_confirmation(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'SO Batch PDF Client']);

        Stock::factory()->create(['product_code' => 'SO-BATCH-PDF', 'quantity' => 0]);
        StockBatch::create([
            'product_code' => 'SO-BATCH-PDF',
            'batch_number' => 'SO-PDF-BATCH-1',
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
                'product_code' => 'SO-BATCH-PDF',
                'product_description' => 'Test Product',
                'qty_ordered' => 5,
                'unit_price' => 2,
            ]],
        ]);
        $so = SalesOrder::latest('id')->firstOrFail();
        $this->post("/sales-orders/{$so->id}/confirm");

        $response = $this->get("/sales-orders/{$so->id}/pdf");
        $response->assertOk();

        $html = view('pdf.sales-order', [
            'salesOrder' => $so->fresh(['client', 'branch', 'createdBy', 'confirmedBy', 'items.batchAllocations.stockBatch']),
            'isDuplicate' => false,
        ])->render();

        $this->assertStringContainsString('SO-PDF-BATCH-1', $html);
    }

    public function test_stock_adjustment_pdf_renders(): void
    {
        $this->actingAsAdmin();

        $product = Stock::factory()->create(['product_code' => 'PDF-ADJ-1', 'quantity' => 10]);

        $this->post('/stock-adjustments', [
            'type' => \App\Models\StockAdjustment::TYPE_STOCK_TAKE,
            'reason' => 'PDF export test',
            'items' => [[
                'product_code' => 'PDF-ADJ-1',
                'product_description' => $product->product_description,
                'qty_counted' => 8,
            ]],
        ]);

        $adjustment = \App\Models\StockAdjustment::where('reason', 'PDF export test')->firstOrFail();

        $response = $this->get("/stock-adjustments/{$adjustment->id}/pdf");
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
