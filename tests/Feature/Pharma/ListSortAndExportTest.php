<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListSortAndExportTest extends TestCase
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

    public function test_products_index_can_be_sorted_by_selling_price(): void
    {
        $this->actingAsAdmin();
        Stock::factory()->create(['product_code' => 'SORT-A', 'product_description' => 'A', 'selling_price' => 50]);
        Stock::factory()->create(['product_code' => 'SORT-B', 'product_description' => 'B', 'selling_price' => 5]);

        $response = $this->get('/products?sort=selling_price&direction=asc');

        $response->assertOk();
        $codes = array_values(array_filter(
            array_map('trim', explode("\n", strip_tags($response->getContent()))),
        ));
        // Cheaper product (SORT-B) should appear before the pricier one (SORT-A) in the rendered order.
        $pos = fn ($needle) => collect($codes)->search(fn ($line) => str_contains($line, $needle));
        $this->assertLessThan($pos('SORT-A'), $pos('SORT-B'));
    }

    public function test_products_sort_rejects_unknown_column_and_falls_back_to_default(): void
    {
        $this->actingAsAdmin();
        Stock::factory()->create(['product_code' => 'SORT-C']);

        // "password" isn't a real stocks column and isn't in the whitelist — must not error out.
        $response = $this->get('/products?sort=password&direction=asc');

        $response->assertOk();
    }

    public function test_products_export_returns_csv_of_selected_ids_only(): void
    {
        $this->actingAsAdmin();
        $a = Stock::factory()->create(['product_code' => 'EXP-A', 'product_description' => 'Export A']);
        Stock::factory()->create(['product_code' => 'EXP-B', 'product_description' => 'Export B']);

        $response = $this->post('/products/export', ['ids' => [$a->id]]);

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('EXP-A', $content);
        $this->assertStringNotContainsString('EXP-B', $content);
    }

    public function test_products_export_without_selection_exports_everything_filtered(): void
    {
        $this->actingAsAdmin();
        Stock::factory()->create(['product_code' => 'EXP-C', 'product_description' => 'Export C']);
        Stock::factory()->create(['product_code' => 'EXP-D', 'product_description' => 'Export D']);

        $response = $this->post('/products/export', []);

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('EXP-C', $content);
        $this->assertStringContainsString('EXP-D', $content);
    }

    /**
     * Every list page's export endpoint should at minimum respond with a
     * downloadable CSV and not error out, whether or not there's data.
     */
    public function test_every_resources_export_endpoint_returns_a_csv(): void
    {
        $this->actingAsAdmin();

        $supplier = Supplier::factory()->create(['status' => 'active']);
        $client = Client::create(['name' => 'Export Smoke Client']);

        $endpoints = [
            '/products/export',
            '/suppliers/export',
            '/clients/export',
            '/purchase-orders/export',
            '/goods-received-notes/export',
            '/quotations/export',
            '/sales-orders/export',
            '/sales-invoices/export',
            '/stock/transfers/bulk-export',
            '/stock-adjustments/export',
            '/user-activity-logs/export',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->post($endpoint, []);
            $response->assertOk();
            $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        }
    }

    public function test_suppliers_index_can_be_sorted_by_name_descending(): void
    {
        $this->actingAsAdmin();
        Supplier::factory()->create(['name' => 'Alpha Supplier', 'status' => 'active']);
        Supplier::factory()->create(['name' => 'Zeta Supplier', 'status' => 'active']);

        $response = $this->get('/suppliers?sort=name&direction=desc');

        $response->assertOk();
        $response->assertSeeTextInOrder(['Zeta Supplier', 'Alpha Supplier']);
    }
}
