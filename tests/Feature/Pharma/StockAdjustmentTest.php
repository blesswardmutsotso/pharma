<?php

namespace Tests\Feature\Pharma;

use App\Models\Stock;
use App\Models\StockAdjustment;
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

    public function test_product_level_adjustment_without_a_batch_number_adjusts_aggregate_quantity_directly(): void
    {
        $this->actingAsRole(User::ROLE_INVENTORY_MANAGER);

        $product = Stock::factory()->create(['product_code' => 'ADJ-2', 'quantity' => 10]);

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
        $this->post("/stock-adjustments/{$adjustment->id}/approve")->assertRedirect();

        $this->assertSame(6, $product->fresh()->quantity);
    }

    public function test_rejected_adjustment_makes_no_stock_changes(): void
    {
        $this->actingAsRole(User::ROLE_INVENTORY_MANAGER);

        $product = Stock::factory()->create(['product_code' => 'ADJ-3', 'quantity' => 10]);

        $this->post('/stock-adjustments', [
            'type' => StockAdjustment::TYPE_OTHER,
            'items' => [[
                'product_code' => 'ADJ-3',
                'product_description' => $product->product_description,
                'qty_counted' => 2,
            ]],
        ])->assertRedirect();

        $adjustment = StockAdjustment::first();
        $this->post("/stock-adjustments/{$adjustment->id}/reject")->assertRedirect();

        $this->assertSame(StockAdjustment::STATUS_REJECTED, $adjustment->fresh()->status);
        $this->assertSame(10, $product->fresh()->quantity);
    }
}
