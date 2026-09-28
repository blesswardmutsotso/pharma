<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Deleting a product used to cascade-delete its stock batches with no
 * warning and no audit trail — several real products vanished from the
 * catalogue this way, breaking search on Sales Orders/Quotations/invoicing
 * even though the goods had genuinely been received. These tests lock in
 * the fix: deletion is blocked whenever the product has real history.
 */
class ProductDeletionGuardTest extends TestCase
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

    public function test_product_with_no_history_can_still_be_deleted_and_is_logged(): void
    {
        $this->actingAsAdmin();
        $product = Stock::factory()->create(['product_code' => 'DEL-1']);

        $response = $this->delete("/products/{$product->id}");

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('stocks', ['product_code' => 'DEL-1']);
        $this->assertDatabaseHas('stock_audit_logs', [
            'action' => 'STOCK_DELETE',
            'product_code' => 'DEL-1',
        ]);
    }

    public function test_product_with_a_stock_batch_cannot_be_deleted(): void
    {
        $this->actingAsAdmin();
        $product = Stock::factory()->create(['product_code' => 'DEL-2']);
        StockBatch::create([
            'product_code' => 'DEL-2',
            'batch_number' => 'DEL-2-BATCH',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => 5,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $response = $this->delete("/products/{$product->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('stock batches', session('error'));
        $this->assertDatabaseHas('stocks', ['product_code' => 'DEL-2']);
        $this->assertDatabaseHas('stock_batches', ['product_code' => 'DEL-2']);
    }

    public function test_product_received_via_a_grn_cannot_be_deleted(): void
    {
        $this->actingAsAdmin();
        $product = Stock::factory()->create(['product_code' => 'DEL-3', 'quantity' => 0]);
        $supplier = Supplier::factory()->create(['status' => 'active']);

        $this->post('/goods-received-notes', [
            'grn_number' => 'GRN-DEL-3',
            'supplier_id' => $supplier->id,
            'received_date' => now()->toDateString(),
            'status' => 'received',
            'items' => [[
                'product_code' => 'DEL-3',
                'product_description' => $product->product_description,
                'qty_received' => 10,
                'unit_cost' => 1,
                'batch_number' => 'DEL-3-BATCH',
                'expiry_date' => now()->addYear()->toDateString(),
                'status' => 'accepted',
            ]],
        ])->assertRedirect(route('goods-received-notes.index'));

        $response = $this->delete("/products/{$product->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('goods received notes', session('error'));
        $this->assertDatabaseHas('stocks', ['product_code' => 'DEL-3']);
        $this->assertDatabaseHas('stock_batches', ['product_code' => 'DEL-3', 'batch_number' => 'DEL-3-BATCH']);
    }

    public function test_product_referenced_on_a_sales_order_cannot_be_deleted(): void
    {
        $this->actingAsAdmin();
        $product = Stock::factory()->create(['product_code' => 'DEL-4']);
        $client = Client::create(['name' => 'Deletion Guard Client']);

        $this->post('/sales-orders', [
            'client_id' => $client->id,
            'currency' => 'USD',
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'DEL-4',
                'product_description' => $product->product_description,
                'qty_ordered' => 2,
                'unit_price' => 5,
            ]],
        ])->assertRedirect();

        $response = $this->delete("/products/{$product->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('sales orders', session('error'));
        $this->assertDatabaseHas('stocks', ['product_code' => 'DEL-4']);
    }

    public function test_roles_outside_the_destroy_middleware_cannot_reach_delete_at_all(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
            'role' => User::ROLE_WAREHOUSE,
        ]);
        $this->actingAs($user);

        $product = Stock::factory()->create(['product_code' => 'DEL-5']);

        $this->delete("/products/{$product->id}")->assertForbidden();
        $this->assertDatabaseHas('stocks', ['product_code' => 'DEL-5']);
    }

    public function test_non_admin_role_allowed_by_middleware_still_cannot_delete(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
            'role' => User::ROLE_MANAGER,
        ]);
        $this->actingAs($user);

        $product = Stock::factory()->create(['product_code' => 'DEL-6']);

        $response = $this->delete("/products/{$product->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only administrators can delete products.');
        $this->assertDatabaseHas('stocks', ['product_code' => 'DEL-6']);
    }
}
