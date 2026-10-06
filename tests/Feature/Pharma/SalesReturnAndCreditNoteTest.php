<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\SalesCreditNote;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\StockAuditLog;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReturnAndCreditNoteTest extends TestCase
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

    protected function actingAsRole(string $role): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
            'role' => $role,
        ]);
        $this->actingAs($user);

        return $user;
    }

    /**
     * Dispatches a 10-unit order for a product with 15% tax and a real
     * buying price, so return tests can verify tax-inclusive crediting and
     * the quarantine batch's cost (not the old bug's selling-price mixup).
     */
    protected function createDispatchedSalesOrder(string $productCode, int $qty = 10, float $unitPrice = 5.00, float $buyingPrice = 2.00, float $taxPercentage = 15.0): SalesOrder
    {
        $client = Client::create(['name' => 'Return Test Client']);

        Stock::factory()->create([
            'product_code' => $productCode,
            'quantity' => 0,
            'buying_price' => $buyingPrice,
            'tax_percentage' => $taxPercentage,
        ]);
        StockBatch::create([
            'product_code' => $productCode,
            'batch_number' => 'B-ORIG-1',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => $qty,
            'unit_cost' => $buyingPrice,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $this->post('/sales-orders', [
            'client_id' => $client->id,
            'currency' => 'USD',
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => $productCode,
                'product_description' => 'Return Test Product',
                'qty_ordered' => $qty,
                'unit_price' => $unitPrice,
            ]],
        ]);

        $so = SalesOrder::latest('id')->firstOrFail();
        $this->post("/sales-orders/{$so->id}/confirm");
        $this->post("/sales-orders/{$so->id}/start-picking");
        $this->post("/sales-orders/{$so->id}/dispatch");

        return $so->fresh();
    }

    public function test_return_creates_quarantine_batch_and_tax_inclusive_credit_note(): void
    {
        $this->actingAsAdmin();
        $so = $this->createDispatchedSalesOrder('RET-1', qty: 10, unitPrice: 5.00, buyingPrice: 2.00, taxPercentage: 15.0);
        $invoice = $so->invoice;
        $invoiceItem = $invoice->items()->firstOrFail();

        // 10 @ $5 = $50, +15% tax = $57.50 total invoice.
        $this->assertSame(57.50, (float) $invoice->total);

        $response = $this->post("/sales-invoices/{$invoice->id}/returns", [
            'lines' => [
                ['sales_invoice_item_id' => $invoiceItem->id, 'qty' => 4],
            ],
            'reason' => 'Damaged on arrival',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $creditNote = SalesCreditNote::where('sales_invoice_id', $invoice->id)->firstOrFail();
        $this->assertSame(SalesCreditNote::TYPE_RETURN, $creditNote->type);
        $this->assertTrue($creditNote->isGoodsReturn());
        // 4 @ $5 = $20, +15% tax = $23.00.
        $this->assertSame(23.00, (float) $creditNote->amount);

        $line = $creditNote->items()->firstOrFail();
        $this->assertSame('RET-1', $line->product_code);
        $this->assertSame(4, $line->qty);
        $this->assertSame(5.00, (float) $line->unit_price);
        $this->assertSame(3.00, (float) $line->tax_amount);
        $this->assertSame(23.00, (float) $line->line_total);

        $batch = $line->stockBatch;
        $this->assertNotNull($batch);
        $this->assertSame(StockBatch::STATUS_QUARANTINE, $batch->status);
        $this->assertSame(4, $batch->qty_on_hand);
        // Cost must be the real buying price, not the selling price (the old bug).
        $this->assertSame(2.00, (float) $batch->unit_cost);
        // Expiry must be inherited from what was actually sold, not a fabricated +1 year.
        $this->assertSame($invoiceItem->expiry_date->toDateString(), $batch->expiry_date->toDateString());

        $invoice->refresh();
        $this->assertSame(34.50, $invoice->balance());
        $this->assertSame(\App\Models\SalesInvoice::STATUS_PARTIALLY_PAID, $invoice->status);

        // Quarantined stock must never count as sellable.
        $this->assertSame(0, Stock::where('product_code', 'RET-1')->value('quantity'));

        $this->assertDatabaseHas('stock_audit_logs', [
            'action' => StockAuditLog::RETURN_GOODS,
            'product_code' => 'RET-1',
            'reference_type' => 'SalesCreditNote',
            'reference_id' => $creditNote->id,
        ]);
    }

    public function test_over_return_is_rejected(): void
    {
        $this->actingAsAdmin();
        $so = $this->createDispatchedSalesOrder('RET-2', qty: 10);
        $invoice = $so->invoice;
        $invoiceItem = $invoice->items()->firstOrFail();

        $response = $this->post("/sales-invoices/{$invoice->id}/returns", [
            'lines' => [
                ['sales_invoice_item_id' => $invoiceItem->id, 'qty' => 11],
            ],
            'reason' => 'Trying to over-return',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame(0, SalesCreditNote::count());
        $this->assertSame(0, StockBatch::where('product_code', 'RET-2')->where('status', StockBatch::STATUS_QUARANTINE)->count());
    }

    public function test_cumulative_returns_cap_at_returnable_qty(): void
    {
        $this->actingAsAdmin();
        $so = $this->createDispatchedSalesOrder('RET-3', qty: 10);
        $invoice = $so->invoice;
        $invoiceItem = $invoice->items()->firstOrFail();

        $this->post("/sales-invoices/{$invoice->id}/returns", [
            'lines' => [['sales_invoice_item_id' => $invoiceItem->id, 'qty' => 6]],
            'reason' => 'First return',
        ])->assertRedirect();

        $this->assertSame(4, $invoiceItem->fresh()->returnableQty());

        // Attempting to return 6 more (total 12) must be rejected — only 4 remain.
        $response = $this->post("/sales-invoices/{$invoice->id}/returns", [
            'lines' => [['sales_invoice_item_id' => $invoiceItem->id, 'qty' => 6]],
            'reason' => 'Second return attempt',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(1, SalesCreditNote::count(), 'Only the first, valid return should exist.');

        // A valid follow-up for exactly what remains must succeed.
        $this->post("/sales-invoices/{$invoice->id}/returns", [
            'lines' => [['sales_invoice_item_id' => $invoiceItem->id, 'qty' => 4]],
            'reason' => 'Second return, correct amount',
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, $invoiceItem->fresh()->returnableQty());
        $this->assertSame(2, SalesCreditNote::count());
    }

    public function test_return_against_cancelled_invoice_is_rejected(): void
    {
        $this->actingAsAdmin();
        $so = $this->createDispatchedSalesOrder('RET-4', qty: 10);
        $invoice = $so->invoice;
        $invoiceItem = $invoice->items()->firstOrFail();
        $invoice->update(['status' => \App\Models\SalesInvoice::STATUS_CANCELLED]);

        $response = $this->post("/sales-invoices/{$invoice->id}/returns", [
            'lines' => [['sales_invoice_item_id' => $invoiceItem->id, 'qty' => 1]],
            'reason' => 'Should not be allowed',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, SalesCreditNote::count());
    }

    public function test_scrap_removes_batch_from_sellable_stock_without_touching_credit_note(): void
    {
        $this->actingAsAdmin();
        $so = $this->createDispatchedSalesOrder('RET-5', qty: 10);
        $invoice = $so->invoice;
        $invoiceItem = $invoice->items()->firstOrFail();

        $this->post("/sales-invoices/{$invoice->id}/returns", [
            'lines' => [['sales_invoice_item_id' => $invoiceItem->id, 'qty' => 3]],
            'reason' => 'Damaged',
        ])->assertRedirect();

        $creditNote = SalesCreditNote::firstOrFail();
        $amountBefore = (float) $creditNote->amount;
        $batch = $creditNote->items()->firstOrFail()->stockBatch;

        $this->post("/stock-batches/{$batch->id}/scrap", [
            'reason' => 'Crushed in transit, unsellable',
        ])->assertRedirect();

        $this->assertSame(StockBatch::STATUS_SCRAPPED, $batch->fresh()->status);
        $this->assertSame(0, Stock::where('product_code', 'RET-5')->value('quantity'));

        // The credit already given to the client must be completely unaffected.
        $this->assertSame($amountBefore, (float) $creditNote->fresh()->amount);

        $this->assertDatabaseHas('stock_audit_logs', [
            'action' => StockAuditLog::SCRAP,
            'product_code' => 'RET-5',
        ]);
    }

    public function test_release_from_quarantine_still_works_and_now_logs_an_audit_entry(): void
    {
        $this->actingAsAdmin();
        $so = $this->createDispatchedSalesOrder('RET-6', qty: 10);
        $invoice = $so->invoice;
        $invoiceItem = $invoice->items()->firstOrFail();

        $this->post("/sales-invoices/{$invoice->id}/returns", [
            'lines' => [['sales_invoice_item_id' => $invoiceItem->id, 'qty' => 5]],
            'reason' => 'Wrong item ordered, repackaging intact',
        ])->assertRedirect();

        $batch = SalesCreditNote::firstOrFail()->items()->firstOrFail()->stockBatch;

        $this->post("/stock-batches/{$batch->id}/release")->assertRedirect();

        $this->assertSame(StockBatch::STATUS_ACTIVE, $batch->fresh()->status);
        $this->assertSame(5, Stock::where('product_code', 'RET-6')->value('quantity'));

        $this->assertDatabaseHas('stock_audit_logs', [
            'action' => StockAuditLog::RETURN_GOODS,
            'product_code' => 'RET-6',
            'reference_type' => 'StockBatch',
            'reference_id' => $batch->id,
        ]);
    }

    public function test_role_without_return_permission_cannot_record_a_return(): void
    {
        $so = $this->createDispatchedSalesOrderAsAdmin('RET-7', 10);
        $invoice = $so->invoice;
        $invoiceItem = $invoice->items()->firstOrFail();

        $this->actingAsRole(User::ROLE_PROCUREMENT);

        $this->post("/sales-invoices/{$invoice->id}/returns", [
            'lines' => [['sales_invoice_item_id' => $invoiceItem->id, 'qty' => 1]],
            'reason' => 'Should be forbidden',
        ])->assertForbidden();
    }

    private function createDispatchedSalesOrderAsAdmin(string $productCode, int $qty): SalesOrder
    {
        $this->actingAsAdmin();

        return $this->createDispatchedSalesOrder($productCode, qty: $qty);
    }
}
