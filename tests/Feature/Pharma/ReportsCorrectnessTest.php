<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsCorrectnessTest extends TestCase
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

    protected function createInvoice(Client $client, string $invoiceNumber, string $status, float $total = 100.0): SalesInvoice
    {
        $so = SalesOrder::create([
            'so_number' => 'SO-' . $invoiceNumber,
            'client_id' => $client->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_INVOICED,
        ]);

        $invoice = SalesInvoice::create([
            'invoice_number' => $invoiceNumber,
            'sales_order_id' => $so->id,
            'client_id' => $client->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => $status,
            'subtotal' => $total,
            'tax_total' => 0,
            'total' => $total,
        ]);

        SalesInvoiceItem::create([
            'sales_invoice_id' => $invoice->id,
            'product_code' => 'RPT-1',
            'product_description' => 'Reported Product',
            'batch_number' => 'RPT-BATCH',
            'expiry_date' => now()->addMonths(6),
            'qty' => 5,
            'unit_price' => $total / 5,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'line_total' => $total,
        ]);

        return $invoice;
    }

    public function test_cancelled_invoice_has_zero_balance_and_is_settled(): void
    {
        $client = Client::create(['name' => 'Cancelled Invoice Client']);
        $invoice = $this->createInvoice($client, 'INV-CANCELLED-1', SalesInvoice::STATUS_CANCELLED, 250);

        $this->assertSame(0.0, $invoice->balance());
        $this->assertTrue($invoice->isSettled());
    }

    public function test_sales_summary_report_excludes_cancelled_invoices(): void
    {
        $this->actingAsAuthenticatedUser();
        $client = Client::create(['name' => 'Report Client']);

        $this->createInvoice($client, 'INV-RPT-LIVE', SalesInvoice::STATUS_UNPAID, 100);
        $this->createInvoice($client, 'INV-RPT-CANCELLED', SalesInvoice::STATUS_CANCELLED, 500);

        $response = $this->get('/reports/sales-summary');

        $response->assertOk();
        $response->assertSeeText('100.00');
        $response->assertDontSeeText('600.00');
    }

    public function test_revenue_report_excludes_cancelled_invoices(): void
    {
        $this->actingAsAuthenticatedUser();
        $client = Client::create(['name' => 'Revenue Client']);

        $this->createInvoice($client, 'INV-REV-LIVE', SalesInvoice::STATUS_UNPAID, 80);
        $this->createInvoice($client, 'INV-REV-CANCELLED', SalesInvoice::STATUS_CANCELLED, 900);

        $response = $this->get('/reports/revenue-report');

        $response->assertOk();
        $response->assertSeeText('80.00');
        $response->assertDontSeeText('980.00');
    }

    public function test_analytics_excludes_cancelled_invoices_from_revenue_and_top_lists(): void
    {
        $this->actingAsAuthenticatedUser();
        $client = Client::create(['name' => 'Analytics Client']);

        $this->createInvoice($client, 'INV-AN-LIVE', SalesInvoice::STATUS_UNPAID, 60);
        $this->createInvoice($client, 'INV-AN-CANCELLED', SalesInvoice::STATUS_CANCELLED, 5000);

        $response = $this->get('/analytics');

        $response->assertOk();
        // The cancelled invoice's huge total must not leak into this month's revenue figure.
        $response->assertDontSeeText('5,060.00');
        $response->assertDontSeeText('5060.00');
    }

    public function test_customer_statement_excludes_cancelled_invoice_from_balance_due(): void
    {
        $this->actingAsAuthenticatedUser();
        $client = Client::create(['name' => 'Statement Client']);

        $this->createInvoice($client, 'INV-STMT-LIVE', SalesInvoice::STATUS_UNPAID, 45);
        $this->createInvoice($client, 'INV-STMT-CANCELLED', SalesInvoice::STATUS_CANCELLED, 700);

        $response = $this->get("/clients/{$client->id}/statement");

        $response->assertOk();
        $response->assertViewHas('balanceDue', 45.0);
    }
}
