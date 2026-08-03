<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyAndTotalsTest extends TestCase
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

    public function test_grn_rejects_a_product_code_not_in_the_catalogue(): void
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create(['status' => 'active']);

        $response = $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-BAD-CODE',
            'supplier_id' => $supplier->id,
            'received_date' => now()->toDateString(),
            'status' => 'received',
            'items' => [[
                'product_code' => 'NOT-IN-CATALOGUE',
                'product_description' => 'Ghost Product',
                'qty_received' => 10,
                'unit_cost' => 1,
                'batch_number' => 'B1',
                'expiry_date' => now()->addYear()->toDateString(),
                'status' => 'accepted',
            ]],
        ]);

        $response->assertSessionHasErrors(['items.0.product_code']);
        $this->assertDatabaseMissing('goods_received_notes', ['grn_number' => 'GRN-BAD-CODE']);
    }

    public function test_grn_inherits_currency_from_its_purchase_order_and_computes_grand_total(): void
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create(['status' => 'active']);
        Stock::factory()->create(['product_code' => 'CUR-1']);

        $this->post('/purchase-orders', [
            'po_number' => 'PO-CUR-1',
            'supplier_id' => $supplier->id,
            'currency' => 'ZWG',
            'order_date' => now()->toDateString(),
            'status' => 'draft',
            'items' => [[
                'product_code' => 'CUR-1',
                'product_description' => 'Currency Test Product',
                'qty_ordered' => 10,
                'unit_cost' => 2,
            ]],
        ]);
        $po = PurchaseOrder::where('po_number', 'PO-CUR-1')->firstOrFail();

        $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-CUR-1',
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'received_date' => now()->toDateString(),
            'status' => 'received',
            'items' => [[
                'product_code' => 'CUR-1',
                'product_description' => 'Currency Test Product',
                'qty_received' => 4,
                'unit_cost' => 2.5,
                'batch_number' => 'CUR-BATCH-1',
                'expiry_date' => now()->addYear()->toDateString(),
                'status' => 'accepted',
            ]],
        ]);

        $grn = GoodsReceivedNote::where('grn_number', 'GRN-CUR-1')->firstOrFail();

        $this->assertSame('ZWG', $grn->currency());
        $this->assertEqualsWithDelta(10.0, $grn->grandTotal(), 0.001);
    }

    public function test_grn_with_no_linked_purchase_order_defaults_to_usd(): void
    {
        $this->actingAsAdmin();
        $supplier = Supplier::factory()->create(['status' => 'active']);
        Stock::factory()->create(['product_code' => 'CUR-2']);

        $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-CUR-2',
            'supplier_id' => $supplier->id,
            'received_date' => now()->toDateString(),
            'status' => 'received',
            'items' => [[
                'product_code' => 'CUR-2',
                'product_description' => 'No PO Product',
                'qty_received' => 2,
                'unit_cost' => 3,
                'batch_number' => 'CUR-BATCH-2',
                'expiry_date' => now()->addYear()->toDateString(),
                'status' => 'accepted',
            ]],
        ]);

        $grn = GoodsReceivedNote::where('grn_number', 'GRN-CUR-2')->firstOrFail();

        $this->assertSame('USD', $grn->currency());
    }

    public function test_sales_invoice_inherits_currency_and_client_po_number_from_its_sales_order(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Currency Invoice Client']);

        $so = SalesOrder::create([
            'so_number' => 'SO-CUR-1',
            'client_id' => $client->id,
            'currency' => 'ZWG',
            'client_po_number' => 'CLIENT-PO-999',
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_INVOICED,
        ]);
        $invoice = SalesInvoice::create([
            'invoice_number' => 'INV-CUR-1',
            'sales_order_id' => $so->id,
            'client_id' => $client->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => SalesInvoice::STATUS_UNPAID,
            'subtotal' => 10, 'tax_total' => 0, 'total' => 10,
        ]);

        $this->assertSame('ZWG', $invoice->currency());
        $this->assertSame('CLIENT-PO-999', $invoice->clientPoNumber());
    }
}
