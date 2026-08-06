<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientSupplierBankingDetailsTest extends TestCase
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

    public function test_client_can_be_created_with_usd_and_zwg_banking_details(): void
    {
        $this->actingAsAdmin();

        $this->post('/clients', [
            'name' => 'Banking Details Client',
            'bank_name' => 'Crown Bank Limited',
            'bank_account_name' => 'Banking Details Client (Pvt) Ltd',
            'bank_account_number' => '111222333',
            'zig_bank_account_number' => '444555666',
        ])->assertRedirect();

        $client = Client::where('name', 'Banking Details Client')->firstOrFail();

        $this->assertSame('Crown Bank Limited', $client->bank_name);
        $this->assertSame('111222333', $client->bank_account_number);
        $this->assertSame('444555666', $client->zig_bank_account_number);

        $response = $this->get("/clients/{$client->id}");
        $response->assertOk();
        $response->assertSeeText('Crown Bank Limited');
        $response->assertSeeText('444555666');
    }

    public function test_client_update_can_change_banking_details(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Update Banking Client']);

        $this->put("/clients/{$client->id}", [
            'name' => 'Update Banking Client',
            'bank_name' => 'New Bank',
            'bank_account_number' => '999',
            'zig_bank_account_number' => '888',
        ])->assertRedirect();

        $this->assertSame('New Bank', $client->fresh()->bank_name);
        $this->assertSame('999', $client->fresh()->bank_account_number);
        $this->assertSame('888', $client->fresh()->zig_bank_account_number);
    }

    public function test_supplier_can_be_created_with_usd_and_zwg_banking_details(): void
    {
        $this->actingAsAdmin();

        $this->post('/suppliers', [
            'name' => 'Banking Details Supplier',
            'status' => 'active',
            'bank_name' => 'Crown Bank Limited',
            'bank_account_name' => 'Banking Details Supplier (Pvt) Ltd',
            'bank_account_number' => '777888999',
            'zig_bank_account_number' => '123123123',
        ])->assertRedirect();

        $supplier = Supplier::where('name', 'Banking Details Supplier')->firstOrFail();

        $this->assertSame('Crown Bank Limited', $supplier->bank_name);
        $this->assertSame('777888999', $supplier->bank_account_number);
        $this->assertSame('123123123', $supplier->zig_bank_account_number);

        $response = $this->get("/suppliers/{$supplier->id}");
        $response->assertOk();
        $response->assertSeeText('Crown Bank Limited');
        $response->assertSeeText('123123123');
    }

    public function test_supplier_edit_page_renders_and_update_changes_banking_details(): void
    {
        $this->actingAsAdmin();
        $supplier = Supplier::create(['name' => 'Update Banking Supplier', 'status' => 'active']);

        $this->get("/suppliers/{$supplier->id}/edit")->assertOk();

        $this->put("/suppliers/{$supplier->id}", [
            'name' => 'Update Banking Supplier',
            'status' => 'active',
            'bank_name' => 'Another Bank',
            'bank_account_number' => '555',
            'zig_bank_account_number' => '666',
        ])->assertRedirect();

        $this->assertSame('Another Bank', $supplier->fresh()->bank_name);
        $this->assertSame('555', $supplier->fresh()->bank_account_number);
        $this->assertSame('666', $supplier->fresh()->zig_bank_account_number);
    }
}
