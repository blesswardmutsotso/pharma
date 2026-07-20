<?php

namespace Tests\Feature\Pharma;

use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ReorderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderWorkflowTest extends TestCase
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

    protected function createDraftPurchaseOrder(Supplier $supplier): PurchaseOrder
    {
        $this->post('/purchase-orders', [
            'po_number' => 'PO-TEST-0001',
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'status' => 'draft',
            'items' => [
                [
                    'product_code' => 'PRD-500',
                    'product_description' => 'Ibuprofen 200mg',
                    'qty_ordered' => 100,
                    'unit_cost' => 1.00,
                ],
            ],
        ]);

        return PurchaseOrder::where('po_number', 'PO-TEST-0001')->firstOrFail();
    }

    public function test_purchase_order_moves_through_submit_approve_close_workflow(): void
    {
        $this->actingAsAuthenticatedUser();
        $supplier = Supplier::factory()->create(['status' => 'active']);
        $po = $this->createDraftPurchaseOrder($supplier);

        $this->assertSame(PurchaseOrder::STATUS_DRAFT, $po->status);

        $this->post("/purchase-orders/{$po->id}/submit")->assertRedirect();
        $this->assertSame(PurchaseOrder::STATUS_SUBMITTED, $po->fresh()->status);

        $this->post("/purchase-orders/{$po->id}/approve")->assertRedirect();
        $po->refresh();
        $this->assertSame(PurchaseOrder::STATUS_APPROVED, $po->status);
        $this->assertNotNull($po->approved_by);

        $this->post("/purchase-orders/{$po->id}/close")->assertRedirect();
        $this->assertSame(PurchaseOrder::STATUS_CLOSED, $po->fresh()->status);
    }

    public function test_purchase_order_cannot_be_approved_before_submission(): void
    {
        $this->actingAsAuthenticatedUser();
        $supplier = Supplier::factory()->create(['status' => 'active']);
        $po = $this->createDraftPurchaseOrder($supplier);

        $this->post("/purchase-orders/{$po->id}/approve")->assertRedirect();

        $this->assertSame(PurchaseOrder::STATUS_DRAFT, $po->fresh()->status);
    }

    public function test_grn_partial_receipt_flags_discrepancy_and_leaves_po_open(): void
    {
        $this->actingAsAuthenticatedUser();
        $supplier = Supplier::factory()->create(['status' => 'active']);
        $po = $this->createDraftPurchaseOrder($supplier);
        $this->post("/purchase-orders/{$po->id}/submit");
        $this->post("/purchase-orders/{$po->id}/approve");

        Stock::factory()->create(['product_code' => 'PRD-500', 'quantity' => 0]);

        $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-TEST-0001',
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'received_date' => now()->toDateString(),
            'status' => 'partial',
            'items' => [
                [
                    'product_code' => 'PRD-500',
                    'product_description' => 'Ibuprofen 200mg',
                    'qty_received' => 60,
                    'unit_cost' => 1.00,
                    'batch_number' => 'BATCH-P1',
                    'expiry_date' => now()->addYear()->toDateString(),
                    'status' => 'accepted',
                ],
            ],
        ])->assertRedirect();

        $poItem = $po->items()->first();
        $this->assertSame(60, $poItem->qty_received);
        $this->assertTrue($poItem->hasDiscrepancy());
        $this->assertSame(-40, $poItem->discrepancy());

        // PO stays approved (not fully received) since only part of the order arrived.
        $this->assertSame(PurchaseOrder::STATUS_APPROVED, $po->fresh()->status);

        $this->assertDatabaseHas('stock_audit_logs', [
            'action' => 'GRN_DISCREPANCY',
            'product_code' => 'PRD-500',
        ]);
    }

    public function test_grn_full_receipt_marks_purchase_order_received(): void
    {
        $this->actingAsAuthenticatedUser();
        $supplier = Supplier::factory()->create(['status' => 'active']);
        $po = $this->createDraftPurchaseOrder($supplier);
        $this->post("/purchase-orders/{$po->id}/submit");
        $this->post("/purchase-orders/{$po->id}/approve");

        Stock::factory()->create(['product_code' => 'PRD-500', 'quantity' => 0]);

        $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-TEST-0002',
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'received_date' => now()->toDateString(),
            'status' => 'received',
            'items' => [
                [
                    'product_code' => 'PRD-500',
                    'product_description' => 'Ibuprofen 200mg',
                    'qty_received' => 100,
                    'unit_cost' => 1.00,
                    'batch_number' => 'BATCH-F1',
                    'expiry_date' => now()->addYear()->toDateString(),
                    'status' => 'accepted',
                ],
            ],
        ])->assertRedirect();

        $this->assertSame(PurchaseOrder::STATUS_RECEIVED, $po->fresh()->status);
        $this->assertFalse($po->items()->first()->hasDiscrepancy());
    }

    public function test_reorder_service_generates_draft_po_for_low_stock_product_with_default_supplier(): void
    {
        $this->actingAsAuthenticatedUser();
        $supplier = Supplier::factory()->create(['status' => 'active']);

        Stock::factory()->create([
            'product_code' => 'PRD-LOW-1',
            'quantity' => 5,
            'reorder_point' => 10,
            'reorder_qty' => 50,
            'default_supplier_id' => $supplier->id,
        ]);

        $created = app(ReorderService::class)->generateDraftPurchaseOrders();

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('purchase_order_items', [
            'product_code' => 'PRD-LOW-1',
            'qty_ordered' => 50,
        ]);

        // Running it again should not create a duplicate while the draft is still open.
        $createdAgain = app(ReorderService::class)->generateDraftPurchaseOrders();
        $this->assertSame(0, $createdAgain);
    }

    public function test_reorder_service_skips_low_stock_products_without_default_supplier(): void
    {
        $this->actingAsAuthenticatedUser();

        Stock::factory()->create([
            'product_code' => 'PRD-LOW-2',
            'quantity' => 1,
            'reorder_point' => 10,
            'reorder_qty' => 20,
            'default_supplier_id' => null,
        ]);

        $created = app(ReorderService::class)->generateDraftPurchaseOrders();

        $this->assertSame(0, $created);
        $this->assertDatabaseMissing('purchase_order_items', ['product_code' => 'PRD-LOW-2']);
    }
}
