<?php

namespace Tests\Feature\Pharma;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ERPModuleTest extends TestCase
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

    protected function actingAsAdminUser(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
            'user_type' => 1,
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_supplier_index_and_create_are_available(): void
    {
        $this->actingAsAuthenticatedUser();

        $index = $this->get('/suppliers');
        $index->assertOk();

        $create = $this->get('/suppliers/create');
        $create->assertOk();
    }

    public function test_supplier_can_be_created(): void
    {
        $this->actingAsAuthenticatedUser();

        $response = $this->post('/suppliers', [
            'name' => 'Quva Pharma Supplier',
            'contact_person' => 'Jane Doe',
            'phone' => '+263771234567',
            'email' => 'supplier@quva.co.zw',
            'tin' => '2001001000',
            'address' => 'Harare',
            'payment_terms' => 'Net 30',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('suppliers', [
            'name' => 'Quva Pharma Supplier',
        ]);
    }

    public function test_supplier_can_be_edited_and_deactivated(): void
    {
        $this->actingAsAuthenticatedUser();

        $supplier = \App\Models\Supplier::factory()->create([
            'name' => 'Original Supplier Name',
            'status' => 'active',
        ]);

        $this->get("/suppliers/{$supplier->id}/edit")->assertOk();

        $updateResponse = $this->put("/suppliers/{$supplier->id}", [
            'name' => 'Updated Supplier Name',
            'status' => 'active',
        ]);
        $updateResponse->assertRedirect(route('suppliers.show', $supplier));
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Updated Supplier Name',
        ]);

        $toggleResponse = $this->post("/suppliers/{$supplier->id}/toggle-status");
        $toggleResponse->assertRedirect();
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'status' => 'inactive',
        ]);
    }

    public function test_purchase_order_index_and_create_are_available(): void
    {
        $this->actingAsAuthenticatedUser();

        $index = $this->get('/purchase-orders');
        $index->assertOk();

        $create = $this->get('/purchase-orders/create');
        $create->assertOk();
    }

    public function test_purchase_order_can_be_created(): void
    {
        $this->actingAsAuthenticatedUser();

        $supplier = \App\Models\Supplier::factory()->create([
            'name' => 'Quva Pharma Supplier',
            'status' => 'active',
        ]);

        $response = $this->post('/purchase-orders', [
            'po_number' => 'PO-2026-001',
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(5)->toDateString(),
            'status' => 'draft',
            'notes' => 'Initial ERP purchase order',
            'items' => [
                [
                    'product_code' => 'SKU-001',
                    'product_description' => 'Amoxicillin 500mg',
                    'qty_ordered' => 10,
                    'unit_cost' => 2.50,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => 'PO-2026-001',
        ]);
    }

    public function test_goods_received_route_is_available(): void
    {
        $this->actingAsAuthenticatedUser();

        $response = $this->get('/goods-received-notes/create');
        $response->assertOk();
    }

    public function test_goods_received_note_can_store_batch_and_expiry_details(): void
    {
        $this->actingAsAuthenticatedUser();

        $supplier = \App\Models\Supplier::factory()->create([
            'name' => 'Batch Supplier',
            'status' => 'active',
        ]);

        $stock = \App\Models\Stock::create([
            'product_code' => 'SKU-001',
            'product_description' => 'Amoxicillin 500mg',
            'buying_price' => 1.80,
            'selling_price' => 3.00,
            'quantity' => 0,
            'tax_code' => 'A',
            'tax_id' => 1,
            'tax_percentage' => 15.00,
            'tax_amount' => 0.45,
            'sales_amount_with_tax' => 3.00,
            'hs_code' => '0000',
        ]);

        $response = $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-2026-001',
            'purchase_order_id' => null,
            'supplier_id' => $supplier->id,
            'received_date' => now()->toDateString(),
            'status' => 'received',
            'notes' => 'Batch tracking verification',
            'items' => [
                [
                    'product_code' => 'SKU-001',
                    'product_description' => 'Amoxicillin 500mg',
                    'qty_received' => 10,
                    'unit_cost' => 2.50,
                    'batch_number' => 'BATCH-2026-001',
                    'expiry_date' => now()->addMonths(6)->toDateString(),
                    'status' => 'accepted',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('goods_received_notes', [
            'grn_number' => 'GRN-2026-001',
        ]);
        $this->assertDatabaseHas('goods_received_note_items', [
            'batch_number' => 'BATCH-2026-001',
            'expiry_date' => now()->addMonths(6)->toDateString(),
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_code' => 'SKU-001',
            'quantity' => 10,
        ]);
    }

    public function test_analytics_page_lists_items_near_expiry(): void
    {
        $this->actingAsAdminUser();

        $supplier = \App\Models\Supplier::factory()->create([
            'name' => 'Expiry Watch Supplier',
            'status' => 'active',
        ]);

        \App\Models\Stock::create([
            'product_code' => 'SKU-EXP-001',
            'product_description' => 'Paracetamol 500mg',
            'buying_price' => 1.60,
            'selling_price' => 2.50,
            'quantity' => 0,
            'tax_code' => 'A',
            'tax_id' => 1,
            'tax_percentage' => 15.00,
            'tax_amount' => 0.38,
            'sales_amount_with_tax' => 2.50,
            'hs_code' => '0000',
        ]);

        $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-EXP-001',
            'purchase_order_id' => null,
            'supplier_id' => $supplier->id,
            'received_date' => now()->toDateString(),
            'status' => 'received',
            'notes' => 'Expiry watch verification',
            'items' => [
                [
                    'product_code' => 'SKU-EXP-001',
                    'product_description' => 'Paracetamol 500mg',
                    'qty_received' => 12,
                    'unit_cost' => 1.60,
                    'batch_number' => 'EXP-BATCH-001',
                    'expiry_date' => now()->addDays(30)->toDateString(),
                    'status' => 'accepted',
                ],
            ],
        ]);

        $response = $this->get('/analytics');

        $response->assertOk();
        $response->assertSeeText('Expiry Watch');
        $response->assertSeeText('SKU-EXP-001');
    }

    public function test_analytics_page_surfaces_restock_recommendations_for_low_stock_items(): void
    {
        $this->actingAsAdminUser();

        \App\Models\Stock::create([
            'product_code' => 'SKU-LOW-001',
            'product_description' => 'Ibuprofen 400mg',
            'buying_price' => 1.80,
            'selling_price' => 2.80,
            'quantity' => 3,
            'reorder_point' => 10,
            'reorder_qty' => 20,
            'tax_code' => 'A',
            'tax_id' => 1,
            'tax_percentage' => 15.00,
            'tax_amount' => 0.42,
            'sales_amount_with_tax' => 2.80,
            'hs_code' => '0000',
        ]);

        $response = $this->get('/analytics');

        $response->assertOk();
        $response->assertSeeText('Restock Recommendations');
        $response->assertSeeText('SKU-LOW-001');
    }

    public function test_stock_transfer_approval_updates_stock_and_records_branch_audit(): void
    {
        $this->actingAsAuthenticatedUser();

        $fromBranch = \App\Models\Branch::create([
            'name' => 'Main Warehouse',
            'code' => 'WH01',
            'address' => 'Harare',
            'phone' => '+263771000001',
            'is_active' => true,
            'is_home' => true,
        ]);

        $toBranch = \App\Models\Branch::create([
            'name' => 'Mutare Branch',
            'code' => 'BR02',
            'address' => 'Mutare',
            'phone' => '+263771000002',
            'is_active' => true,
            'is_home' => false,
        ]);

        \App\Models\Stock::create([
            'product_code' => 'SKU-TRF-001',
            'product_description' => 'Cough Syrup',
            'buying_price' => 4.00,
            'selling_price' => 6.00,
            'quantity' => 25,
            'tax_code' => 'A',
            'tax_id' => 1,
            'tax_percentage' => 15.00,
            'tax_amount' => 0.90,
            'sales_amount_with_tax' => 6.00,
            'hs_code' => '0000',
        ]);

        $transfer = \App\Models\StockTransfer::create([
            'transfer_no' => 'TRF-20260711-0001',
            'transfer_type' => \App\Models\StockTransfer::TYPE_OUTGOING,
            'from_branch_id' => $fromBranch->id,
            'to_branch_id' => $toBranch->id,
            'status' => \App\Models\StockTransfer::STATUS_PENDING,
            'total_items' => 1,
            'total_qty' => 10,
            'requested_by' => auth()->id(),
            'approved_by' => null,
        ]);

        \App\Models\StockTransferItem::create([
            'transfer_id' => $transfer->id,
            'product_code' => 'SKU-TRF-001',
            'product_description' => 'Cough Syrup',
            'qty_requested' => 10,
            'buying_price' => 4.00,
            'selling_price' => 6.00,
            'tax_code' => 'A',
            'notes' => 'Warehouse governance verification',
        ]);

        $response = $this->post("/stock/transfers/{$transfer->id}/approve");

        $response->assertRedirect();
        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transfer->id,
            'status' => \App\Models\StockTransfer::STATUS_APPROVED,
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_code' => 'SKU-TRF-001',
            'quantity' => 15,
        ]);
        $this->assertDatabaseHas('stock_audit_logs', [
            'reference_type' => 'StockTransfer',
            'reference_id' => $transfer->id,
            'action' => \App\Models\StockAuditLog::TRANSFER_OUT,
        ]);
    }
}
