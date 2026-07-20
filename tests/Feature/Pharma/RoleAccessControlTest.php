<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessControlTest extends TestCase
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

    public function test_sales_user_cannot_create_a_purchase_order(): void
    {
        $this->actingAsRole(User::ROLE_SALES);

        $response = $this->get('/purchase-orders/create');

        $response->assertForbidden();
    }

    public function test_procurement_user_can_create_a_purchase_order(): void
    {
        $this->actingAsRole(User::ROLE_PROCUREMENT);
        $supplier = Supplier::factory()->create(['status' => 'active']);

        $response = $this->post('/purchase-orders', [
            'po_number' => 'PO-RBAC-1',
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'status' => 'draft',
            'items' => [[
                'product_code' => 'RBAC-1',
                'product_description' => 'Test',
                'qty_ordered' => 1,
                'unit_cost' => 1,
            ]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', ['po_number' => 'PO-RBAC-1']);
    }

    public function test_warehouse_user_cannot_create_a_sales_order(): void
    {
        $this->actingAsRole(User::ROLE_WAREHOUSE);

        $response = $this->get('/sales-orders/create');

        $response->assertForbidden();
    }

    public function test_sales_user_can_create_a_sales_order(): void
    {
        $this->actingAsRole(User::ROLE_SALES);
        $client = Client::create(['name' => 'RBAC Client']);

        $response = $this->post('/sales-orders', [
            'so_number' => 'SO-RBAC-1',
            'client_id' => $client->id,
            'order_date' => now()->toDateString(),
            'items' => [[
                'product_code' => 'RBAC-2',
                'product_description' => 'Test',
                'qty_ordered' => 1,
                'unit_price' => 1,
            ]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales_orders', ['so_number' => 'SO-RBAC-1']);
    }

    public function test_sales_user_cannot_record_a_payment(): void
    {
        $this->actingAsRole(User::ROLE_SALES);
        $client = Client::create(['name' => 'RBAC Payment Client']);

        $response = $this->post("/clients/{$client->id}/payments", [
            'amount' => 10,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [],
        ]);

        $response->assertForbidden();
    }

    public function test_auditor_can_view_but_not_mutate_anything(): void
    {
        $this->actingAsRole(User::ROLE_AUDITOR);
        $supplier = Supplier::factory()->create(['status' => 'active']);

        $this->get('/products')->assertOk();
        $this->get('/purchase-orders')->assertOk();
        $this->get('/reports')->assertOk();

        // Auditor is blocked from any mutating request, globally, regardless
        // of whether a given controller's own role list would allow it.
        $response = $this->post('/suppliers', [
            'name' => 'Should Not Be Created',
            'status' => 'active',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('suppliers', ['name' => 'Should Not Be Created']);
    }

    public function test_finance_user_can_view_invoices_but_not_create_products(): void
    {
        $this->actingAsRole(User::ROLE_FINANCE);

        $this->get('/sales-invoices')->assertOk();

        $response = $this->get('/products/create');
        $response->assertForbidden();
    }

    public function test_inventory_manager_can_manage_products(): void
    {
        $this->actingAsRole(User::ROLE_INVENTORY_MANAGER);

        $response = $this->post('/products', [
            'product_code' => 'RBAC-INV-1',
            'product_description' => 'Test Product',
            'buying_price' => 1,
            'selling_price' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('stocks', ['product_code' => 'RBAC-INV-1']);
    }

    public function test_non_admin_user_cannot_create_or_promote_users(): void
    {
        $this->actingAsRole(User::ROLE_SALES);

        $response = $this->post('/users', [
            'new_name' => 'Sneaky Admin',
            'new_email' => 'sneaky@example.com',
            'new_password' => 'Secure123!',
            'new_password_confirmation' => 'Secure123!',
            'new_user_type' => 1,
            'new_role' => User::ROLE_ADMIN,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.com']);
    }

    public function test_non_admin_user_cannot_edit_or_deactivate_another_user(): void
    {
        $this->actingAsRole(User::ROLE_SALES);
        $victim = \App\Models\User::factory()->create(['role' => User::ROLE_FINANCE]);

        $this->put("/users/{$victim->id}/admin-update", [
            'name' => $victim->name,
            'email' => $victim->email,
            'user_type' => 0,
            'role' => User::ROLE_ADMIN,
        ])->assertForbidden();

        $this->post("/users/{$victim->id}/toggle-active")->assertForbidden();

        $this->assertSame(User::ROLE_FINANCE, $victim->fresh()->role);
    }

    public function test_sales_user_cannot_approve_a_stock_transfer(): void
    {
        $this->actingAsRole(User::ROLE_SALES);

        $fromBranch = \App\Models\Branch::create(['name' => 'A', 'code' => 'RBAC-A', 'is_active' => true]);
        $toBranch = \App\Models\Branch::create(['name' => 'B', 'code' => 'RBAC-B', 'is_active' => true]);

        $transfer = \App\Models\StockTransfer::create([
            'transfer_no' => 'TRF-RBAC-1',
            'transfer_type' => \App\Models\StockTransfer::TYPE_OUTGOING,
            'from_branch_id' => $fromBranch->id,
            'to_branch_id' => $toBranch->id,
            'status' => \App\Models\StockTransfer::STATUS_PENDING,
            'total_items' => 0,
            'total_qty' => 0,
            'requested_by' => auth()->id(),
        ]);

        $this->post("/stock/transfers/{$transfer->id}/approve")->assertForbidden();
        $this->assertSame(\App\Models\StockTransfer::STATUS_PENDING, $transfer->fresh()->status);
    }
}
