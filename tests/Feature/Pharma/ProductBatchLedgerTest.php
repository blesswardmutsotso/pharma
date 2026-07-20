<?php

namespace Tests\Feature\Pharma;

use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBatchLedgerTest extends TestCase
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

    public function test_product_can_be_created_with_reorder_point(): void
    {
        $this->actingAsAuthenticatedUser();

        $response = $this->post('/products', [
            'product_code' => 'PRD-100',
            'product_description' => 'Amoxicillin 500mg',
            'category' => 'Antibiotics',
            'buying_price' => 3.50,
            'selling_price' => 5.00,
            'reorder_point' => 20,
            'reorder_qty' => 50,
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Stock::where('product_code', 'PRD-100')->firstOrFail();
        $this->assertSame(0, $product->quantity);
        $this->assertSame(20, $product->reorder_point);
        $this->assertSame(50, $product->reorder_qty);
    }

    public function test_receiving_a_grn_creates_a_batch_and_syncs_aggregate_quantity(): void
    {
        $this->actingAsAuthenticatedUser();

        $product = Stock::factory()->create([
            'product_code' => 'PRD-200',
            'product_description' => 'Paracetamol 500mg',
            'quantity' => 0,
            'reorder_point' => 10,
        ]);

        $supplier = Supplier::factory()->create(['status' => 'active']);

        $response = $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-0001',
            'supplier_id' => $supplier->id,
            'received_date' => now()->toDateString(),
            'status' => 'received',
            'items' => [
                [
                    'product_code' => 'PRD-200',
                    'product_description' => 'Paracetamol 500mg',
                    'qty_received' => 40,
                    'unit_cost' => 1.20,
                    'batch_number' => 'BATCH-A',
                    'expiry_date' => now()->addYear()->toDateString(),
                    'status' => 'accepted',
                ],
            ],
        ]);

        $response->assertRedirect(route('goods-received-notes.index'));

        $product->refresh();
        $this->assertSame(40, $product->quantity);

        $batch = StockBatch::where('product_code', 'PRD-200')->firstOrFail();
        $this->assertSame('BATCH-A', $batch->batch_number);
        $this->assertSame(40, $batch->qty_on_hand);
        $this->assertSame(StockBatch::STATUS_ACTIVE, $batch->status);
    }

    public function test_rejected_grn_line_does_not_create_a_batch_or_increase_stock(): void
    {
        $this->actingAsAuthenticatedUser();

        $product = Stock::factory()->create([
            'product_code' => 'PRD-300',
            'quantity' => 0,
        ]);

        $supplier = Supplier::factory()->create(['status' => 'active']);

        $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-0002',
            'supplier_id' => $supplier->id,
            'received_date' => now()->toDateString(),
            'status' => 'received',
            'items' => [
                [
                    'product_code' => 'PRD-300',
                    'product_description' => $product->product_description,
                    'qty_received' => 10,
                    'unit_cost' => 1.00,
                    'batch_number' => 'BATCH-B',
                    'expiry_date' => now()->addMonths(6)->toDateString(),
                    'status' => 'rejected',
                ],
            ],
        ]);

        $product->refresh();
        $this->assertSame(0, $product->quantity);
        $this->assertSame(0, StockBatch::where('product_code', 'PRD-300')->count());
    }

    public function test_low_stock_scope_reflects_reorder_point(): void
    {
        $this->actingAsAuthenticatedUser();

        Stock::factory()->create(['product_code' => 'PRD-LOW', 'quantity' => 5, 'reorder_point' => 10]);
        Stock::factory()->create(['product_code' => 'PRD-OK', 'quantity' => 50, 'reorder_point' => 10]);

        $low = Stock::where('product_code', 'PRD-LOW')->first();
        $ok  = Stock::where('product_code', 'PRD-OK')->first();

        $this->assertTrue($low->isLowStock());
        $this->assertFalse($ok->isLowStock());
    }

    public function test_expiring_within_scope_includes_only_batches_in_window(): void
    {
        $this->actingAsAuthenticatedUser();

        $product = Stock::factory()->create(['product_code' => 'PRD-EXP']);

        StockBatch::create([
            'product_code' => 'PRD-EXP',
            'batch_number' => 'SOON',
            'expiry_date' => now()->addDays(30),
            'qty_on_hand' => 10,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        StockBatch::create([
            'product_code' => 'PRD-EXP',
            'batch_number' => 'FAR',
            'expiry_date' => now()->addDays(400),
            'qty_on_hand' => 10,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $expiring = StockBatch::expiringWithin(90)->pluck('batch_number');

        $this->assertTrue($expiring->contains('SOON'));
        $this->assertFalse($expiring->contains('FAR'));
    }

    public function test_quarantined_batch_can_be_released_into_sellable_stock(): void
    {
        $this->actingAsAuthenticatedUser();

        $product = Stock::factory()->create(['product_code' => 'PRD-QTN', 'quantity' => 0]);
        $batch = StockBatch::create([
            'product_code' => 'PRD-QTN',
            'batch_number' => 'QTN-BATCH-1',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 15,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_QUARANTINE,
        ]);
        $product->syncQuantityFromBatches();
        $this->assertSame(0, $product->fresh()->quantity);

        $response = $this->post("/stock-batches/{$batch->id}/release");
        $response->assertRedirect();

        $this->assertSame(StockBatch::STATUS_ACTIVE, $batch->fresh()->status);
        $this->assertSame(15, $product->fresh()->quantity);
    }

    public function test_active_batch_cannot_be_released_again(): void
    {
        $this->actingAsAuthenticatedUser();

        Stock::factory()->create(['product_code' => 'PRD-ACT', 'quantity' => 5]);
        $batch = StockBatch::create([
            'product_code' => 'PRD-ACT',
            'batch_number' => 'ACT-BATCH-1',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 5,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $response = $this->post("/stock-batches/{$batch->id}/release");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
