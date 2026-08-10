<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPrintButtonsTest extends TestCase
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

    public function test_quotations_index_shows_a_print_link(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Print Button Client']);
        $quotation = Quotation::create([
            'quote_number' => 'QUO-PRINT-1',
            'client_id' => $client->id,
            'quote_date' => now()->toDateString(),
            'status' => Quotation::STATUS_DRAFT,
        ]);

        $response = $this->get('/quotations');

        $response->assertOk();
        $response->assertSee(route('quotations.pdf', $quotation), false);
        $response->assertSeeText('Print');
    }

    public function test_sales_orders_index_shows_a_print_link(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Print Button SO Client']);
        $salesOrder = SalesOrder::create([
            'so_number' => 'SO-PRINT-1',
            'client_id' => $client->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_DRAFT,
        ]);

        $response = $this->get('/sales-orders');

        $response->assertOk();
        $response->assertSee(route('sales-orders.pdf', $salesOrder), false);
        $response->assertSeeText('Print');
    }

    public function test_sales_invoices_index_shows_a_print_link(): void
    {
        $this->actingAsAdmin();
        $client = Client::create(['name' => 'Print Button Invoice Client']);
        $so = SalesOrder::create([
            'so_number' => 'SO-PRINT-INV-1',
            'client_id' => $client->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_INVOICED,
        ]);
        $invoice = SalesInvoice::create([
            'invoice_number' => 'INV-PRINT-1',
            'sales_order_id' => $so->id,
            'client_id' => $client->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => SalesInvoice::STATUS_UNPAID,
            'subtotal' => 10, 'tax_total' => 0, 'total' => 10,
        ]);

        $response = $this->get('/sales-invoices');

        $response->assertOk();
        $response->assertSee(route('sales-invoices.pdf', $invoice), false);
        $response->assertSeeText('Print');
    }
}
