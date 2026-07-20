<?php

namespace Tests\Feature\Pharma;

use App\Models\Branch;
use App\Models\Client;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiLocationInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAuthenticatedUser(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
            'role' => User::ROLE_ADMIN,
        ]);
        $this->actingAs($user);

        return $user;
    }

    protected function secondaryBranch(): Branch
    {
        return Branch::create([
            'name' => 'Mutare Branch',
            'code' => 'BR-MUT',
            'is_active' => true,
            'is_home' => false,
        ]);
    }

    public function test_grn_received_at_a_branch_creates_batches_scoped_to_that_branch(): void
    {
        $this->actingAsAuthenticatedUser();
        $home = Branch::homeOrNull();
        $mutare = $this->secondaryBranch();
        $supplier = Supplier::factory()->create(['status' => 'active']);
        Stock::factory()->create(['product_code' => 'ML-1', 'quantity' => 0]);

        $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-ML-1',
            'supplier_id' => $supplier->id,
            'branch_id' => $mutare->id,
            'received_date' => now()->toDateString(),
            'status' => 'received',
            'items' => [[
                'product_code' => 'ML-1',
                'product_description' => 'Multi-location test product',
                'qty_received' => 25,
                'unit_cost' => 1.5,
                'batch_number' => 'ML-BATCH-1',
                'expiry_date' => now()->addYear()->toDateString(),
                'status' => 'accepted',
            ]],
        ])->assertRedirect();

        $batch = StockBatch::where('product_code', 'ML-1')->firstOrFail();
        $this->assertSame($mutare->id, $batch->branch_id);

        $product = Stock::where('product_code', 'ML-1')->first();
        $this->assertSame(25, $product->quantityAtBranch($mutare->id));
        $this->assertSame(0, $product->quantityAtBranch($home->id));
        // Global aggregate is unaffected by which branch received it.
        $this->assertSame(25, $product->fresh()->quantity);
    }

    public function test_sales_order_scoped_to_a_branch_only_allocates_from_that_branchs_batches(): void
    {
        $this->actingAsAuthenticatedUser();
        $home = Branch::homeOrNull();
        $mutare = $this->secondaryBranch();
        $client = Client::create(['name' => 'Multi-location Client']);

        Stock::factory()->create(['product_code' => 'ML-2', 'quantity' => 0]);

        // All stock physically sits at the home branch...
        StockBatch::create([
            'product_code' => 'ML-2',
            'branch_id' => $home->id,
            'batch_number' => 'HOME-BATCH',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 50,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        // ...but this order is fulfilled from Mutare, which has none.
        $this->post('/sales-orders', [
            'so_number' => 'SO-ML-1',
            'client_id' => $client->id,
            'branch_id' => $mutare->id,
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'ML-2',
                'product_description' => 'Multi-location test product',
                'qty_ordered' => 10,
                'unit_price' => 5,
            ]],
        ]);

        $so = SalesOrder::where('so_number', 'SO-ML-1')->firstOrFail();
        $response = $this->post("/sales-orders/{$so->id}/confirm");

        $response->assertRedirect();
        $this->assertSame(SalesOrder::STATUS_DRAFT, $so->fresh()->status);
        $this->assertStringContainsString('Insufficient stock', session('error'));
    }

    public function test_outgoing_transfer_between_branches_moves_the_batch_and_preserves_its_identity(): void
    {
        $this->actingAsAuthenticatedUser();
        $home = Branch::homeOrNull();
        $mutare = $this->secondaryBranch();

        Stock::factory()->create(['product_code' => 'ML-3', 'quantity' => 40]);

        $sourceBatch = StockBatch::create([
            'product_code' => 'ML-3',
            'branch_id' => $home->id,
            'batch_number' => 'TRF-BATCH',
            'expiry_date' => now()->addMonths(8),
            'qty_on_hand' => 40,
            'unit_cost' => 2,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $transfer = StockTransfer::create([
            'transfer_no' => 'TRF-ML-0001',
            'transfer_type' => StockTransfer::TYPE_OUTGOING,
            'from_branch_id' => $home->id,
            'to_branch_id' => $mutare->id,
            'status' => StockTransfer::STATUS_PENDING,
            'total_items' => 1,
            'total_qty' => 15,
            'requested_by' => auth()->id(),
        ]);

        StockTransferItem::create([
            'transfer_id' => $transfer->id,
            'product_code' => 'ML-3',
            'product_description' => 'Multi-location test product',
            'qty_requested' => 15,
            'buying_price' => 2,
            'selling_price' => 3,
        ]);

        $this->post("/stock/transfers/{$transfer->id}/approve")->assertRedirect();

        $sourceBatch->refresh();
        $this->assertSame(25, $sourceBatch->qty_on_hand);

        $destinationBatch = StockBatch::where('product_code', 'ML-3')
            ->where('branch_id', $mutare->id)
            ->where('batch_number', 'TRF-BATCH')
            ->first();

        $this->assertNotNull($destinationBatch);
        $this->assertSame(15, $destinationBatch->qty_on_hand);
        $this->assertEquals($sourceBatch->expiry_date->toDateString(), $destinationBatch->expiry_date->toDateString());

        // Global aggregate quantity still moves exactly as it did before batch-level tracking existed.
        $this->assertSame(25, Stock::where('product_code', 'ML-3')->first()->quantity);
    }
}
