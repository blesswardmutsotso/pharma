<?php

namespace Tests\Feature\Pharma;

use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_warehouse_user_can_submit_a_batch_level_stock_take_and_inventory_manager_can_approve_it(): void
    {
        $this->actingAsRole(User::ROLE_WAREHOUSE);

        $product = Stock::factory()->create(['product_code' => 'ADJ-1', 'quantity' => 20]);
        $batch = StockBatch::create([
            'product_code' => 'ADJ-1',
            'batch_number' => 'ADJ-BATCH-1',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 20,
            'unit_cost' => 2,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $response = $this->post('/stock-adjustments', [
            'type' => StockAdjustment::TYPE_STOCK_TAKE,
            'reason' => 'Quarterly cycle count',
            'items' => [[
                'product_code' => 'ADJ-1',
                'product_description' => $product->product_description,
                'batch_number' => 'ADJ-BATCH-1',
                'qty_counted' => 17,
            ]],
        ]);

        $response->assertRedirect();
        $adjustment = StockAdjustment::where('reason', 'Quarterly cycle count')->firstOrFail();
        $this->assertSame(StockAdjustment::STATUS_SUBMITTED, $adjustment->status);

        $item = $adjustment->items()->firstOrFail();
        $this->assertSame(20, $item->qty_system);
        $this->assertSame(17, $item->qty_counted);
        $this->assertSame(-3, $item->qty_variance);

        // Warehouse role cannot approve — only inventory_manager/admin can.
        $this->post("/stock-adjustments/{$adjustment->id}/approve")->assertForbidden();

        $this->actingAsRole(User::ROLE_INVENTORY_MANAGER);
        $this->post("/stock-adjustments/{$adjustment->id}/approve")->assertRedirect();

        $this->assertSame(StockAdjustment::STATUS_APPROVED, $adjustment->fresh()->status);
        $this->assertSame(17, $batch->fresh()->qty_on_hand);
        $this->assertSame(17, $product->fresh()->quantity);

        $this->assertDatabaseHas('stock_audit_logs', [
            'action' => 'ADJUSTMENT',
            'product_code' => 'ADJ-1',
            'qty_before' => 20,
            'qty_after' => 17,
        ]);
    }

    /**
     * A shortfall counted without naming a specific batch (e.g. damage spread
     * across shelf stock) must come out of real batches — never just the
     * aggregate Stock.quantity cache, which Sales Orders/invoicing never read.
     */
    public function test_shrinkage_without_a_batch_number_is_absorbed_from_real_batches_oldest_expiry_first(): void
    {
        $this->actingAsRole(User::ROLE_INVENTORY_MANAGER);

        $product = Stock::factory()->create(['product_code' => 'ADJ-2', 'quantity' => 10]);
        $oldBatch = StockBatch::create([
            'product_code' => 'ADJ-2',
            'batch_number' => 'ADJ-2-OLD',
            'expiry_date' => now()->addMonths(3),
            'qty_on_hand' => 4,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);
        $newBatch = StockBatch::create([
            'product_code' => 'ADJ-2',
            'batch_number' => 'ADJ-2-NEW',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 6,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $this->post('/stock-adjustments', [
            'type' => StockAdjustment::TYPE_DAMAGE,
            'reason' => 'Water damage in storeroom',
            'items' => [[
                'product_code' => 'ADJ-2',
                'product_description' => $product->product_description,
                'qty_counted' => 6,
            ]],
        ])->assertRedirect();

        $adjustment = StockAdjustment::where('reason', 'Water damage in storeroom')->firstOrFail();
        $item = $adjustment->items()->firstOrFail();
        $this->assertSame(10, $item->qty_system);
        $this->assertSame(-4, $item->qty_variance);
        $this->assertNull($item->stock_batch_id);

        $this->post("/stock-adjustments/{$adjustment->id}/approve")->assertRedirect();

        // The 4-unit shortfall must come out of the soonest-expiring batch
        // first, not out of the aggregate number.
        $this->assertSame(0, $oldBatch->fresh()->qty_on_hand);
        $this->assertSame(StockBatch::STATUS_DEPLETED, $oldBatch->fresh()->status);
        $this->assertSame(6, $newBatch->fresh()->qty_on_hand);
        $this->assertSame(6, $product->fresh()->quantity);
    }

    /**
     * Counting MORE than the system has, with no batch named, is ambiguous —
     * pharma stock cannot exist without a batch/expiry, so this must be
     * rejected rather than silently inflating the aggregate quantity.
     */
    public function test_found_stock_without_batch_and_expiry_is_rejected_at_submission(): void
    {
        $this->actingAsRole(User::ROLE_INVENTORY_MANAGER);

        $product = Stock::factory()->create(['product_code' => 'ADJ-3', 'quantity' => 0]);
        StockBatch::create([
            'product_code' => 'ADJ-3',
            'batch_number' => 'ADJ-3-EXISTING',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 2,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $response = $this->post('/stock-adjustments', [
            'type' => StockAdjustment::TYPE_STOCK_TAKE,
            'items' => [[
                'product_code' => 'ADJ-3',
                'product_description' => $product->product_description,
                'qty_counted' => 8,
            ]],
        ]);

        $response->assertSessionHasErrors();
        $this->assertSame(0, StockAdjustment::count());
        $this->assertSame(2, StockBatch::where('product_code', 'ADJ-3')->sum('qty_on_hand'));
    }

    /**
     * Found stock WITH a batch number and expiry date must become a real,
     * FEFO-allocatable batch on approval, not just a bigger aggregate number.
     */
    public function test_found_stock_with_batch_and_expiry_creates_a_real_batch_on_approval(): void
    {
        $this->actingAsRole(User::ROLE_INVENTORY_MANAGER);

        $product = Stock::factory()->create(['product_code' => 'ADJ-4', 'quantity' => 0, 'buying_price' => 2.50]);

        $response = $this->post('/stock-adjustments', [
            'type' => StockAdjustment::TYPE_STOCK_TAKE,
            'reason' => 'Found untracked stock',
            'items' => [[
                'product_code' => 'ADJ-4',
                'product_description' => $product->product_description,
                'batch_number' => 'FOUND-BATCH-1',
                'expiry_date' => now()->addYear()->toDateString(),
                'qty_counted' => 5,
            ]],
        ]);

        $response->assertSessionDoesntHaveErrors();
        $adjustment = StockAdjustment::where('reason', 'Found untracked stock')->firstOrFail();
        $item = $adjustment->items()->firstOrFail();
        $this->assertSame(0, $item->qty_system);
        $this->assertSame(5, $item->qty_variance);
        $this->assertNull($item->stock_batch_id);

        $this->post("/stock-adjustments/{$adjustment->id}/approve")->assertRedirect();

        $batch = StockBatch::where('product_code', 'ADJ-4')->where('batch_number', 'FOUND-BATCH-1')->firstOrFail();
        $this->assertSame(5, $batch->qty_on_hand);
        $this->assertSame(StockBatch::STATUS_ACTIVE, $batch->status);
        $this->assertSame('StockAdjustment', $batch->source_type);
        $this->assertSame($adjustment->id, $batch->source_id);
        $this->assertSame(5, $product->fresh()->quantity);
    }

    /**
     * Guards against a stale/legacy submitted adjustment (created before this
     * fix, or by a bypassed request) that still lacks batch/expiry info for a
     * found-stock line — approval must refuse rather than fabricate data.
     */
    public function test_approval_is_blocked_when_found_stock_item_is_missing_batch_info(): void
    {
        $this->actingAsRole(User::ROLE_INVENTORY_MANAGER);

        $product = Stock::factory()->create(['product_code' => 'ADJ-5', 'quantity' => 0]);

        $adjustment = StockAdjustment::create([
            'adjustment_no' => StockAdjustment::generateAdjustmentNo(),
            'type' => StockAdjustment::TYPE_STOCK_TAKE,
            'status' => StockAdjustment::STATUS_SUBMITTED,
        ]);

        StockAdjustmentItem::create([
            'stock_adjustment_id' => $adjustment->id,
            'product_code' => 'ADJ-5',
            'product_description' => $product->product_description,
            'qty_system' => 0,
            'qty_counted' => 5,
            'qty_variance' => 5,
            'unit_cost' => 1,
        ]);

        $response = $this->post("/stock-adjustments/{$adjustment->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame(StockAdjustment::STATUS_SUBMITTED, $adjustment->fresh()->status);
        $this->assertSame(0, StockBatch::where('product_code', 'ADJ-5')->count());
        $this->assertSame(0, $product->fresh()->quantity);
    }

    /**
     * Same question as the GRN case, via Stock Adjustment: existing stock
     * of 7 plus a "found 10 more" adjustment must total 17, not replace the
     * existing 7 with the newly found 10.
     */
    public function test_found_stock_adjustment_adds_to_existing_stock_instead_of_replacing_it(): void
    {
        $this->actingAsRole(User::ROLE_INVENTORY_MANAGER);

        $product = Stock::factory()->create(['product_code' => 'ADJ-7', 'quantity' => 7]);
        StockBatch::create([
            'product_code' => 'ADJ-7',
            'batch_number' => 'ADJ-7-EXISTING',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 7,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $this->post('/stock-adjustments', [
            'type' => StockAdjustment::TYPE_STOCK_TAKE,
            'reason' => 'Found extra stock',
            'items' => [[
                'product_code' => 'ADJ-7',
                'product_description' => $product->product_description,
                'batch_number' => 'ADJ-7-NEW',
                'expiry_date' => now()->addYear()->toDateString(),
                'qty_counted' => 17,
            ]],
        ])->assertRedirect();

        $adjustment = StockAdjustment::where('reason', 'Found extra stock')->firstOrFail();
        $item = $adjustment->items()->firstOrFail();
        $this->assertSame(7, $item->qty_system, 'System total before the count should be the existing 7.');
        $this->assertSame(10, $item->qty_variance, 'Counting 17 against a system total of 7 is a +10 variance.');

        $this->post("/stock-adjustments/{$adjustment->id}/approve")->assertRedirect();

        $this->assertSame(17, $product->fresh()->quantity, 'Existing 7 plus found 10 must total 17, not replace it with 10.');
        $this->assertSame(7, StockBatch::where('batch_number', 'ADJ-7-EXISTING')->value('qty_on_hand'));
        $this->assertSame(10, StockBatch::where('batch_number', 'ADJ-7-NEW')->value('qty_on_hand'));
    }

    public function test_rejected_adjustment_makes_no_stock_changes(): void
    {
        $this->actingAsRole(User::ROLE_INVENTORY_MANAGER);

        $product = Stock::factory()->create(['product_code' => 'ADJ-6', 'quantity' => 10]);
        StockBatch::create([
            'product_code' => 'ADJ-6',
            'batch_number' => 'ADJ-6-BATCH',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 10,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $this->post('/stock-adjustments', [
            'type' => StockAdjustment::TYPE_OTHER,
            'items' => [[
                'product_code' => 'ADJ-6',
                'product_description' => $product->product_description,
                'qty_counted' => 2,
            ]],
        ])->assertRedirect();

        $adjustment = StockAdjustment::firstOrFail();
        $this->post("/stock-adjustments/{$adjustment->id}/reject")->assertRedirect();

        $this->assertSame(StockAdjustment::STATUS_REJECTED, $adjustment->fresh()->status);
        $this->assertSame(10, $product->fresh()->quantity);
        $this->assertSame(10, StockBatch::where('product_code', 'ADJ-6')->sum('qty_on_hand'));
    }
}
