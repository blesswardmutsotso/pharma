<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesInvoicingTest extends TestCase
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

    protected function createDispatchedSalesOrder(string $productCode, int $qty, float $unitPrice = 5.00): SalesOrder
    {
        $client = Client::create(['name' => 'Bulawayo Pharmacy']);

        Stock::factory()->create(['product_code' => $productCode, 'quantity' => 0]);
        StockBatch::create([
            'product_code' => $productCode,
            'batch_number' => 'B-INV-1',
            'expiry_date' => now()->addYear(),
            'qty_on_hand' => $qty,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        $this->post('/sales-orders', [
            'so_number' => 'SO-INV-0001',
            'client_id' => $client->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_code' => $productCode,
                    'product_description' => 'Test Product',
                    'qty_ordered' => $qty,
                    'unit_price' => $unitPrice,
                ],
            ],
        ]);

        $so = SalesOrder::where('so_number', 'SO-INV-0001')->firstOrFail();
        $this->post("/sales-orders/{$so->id}/confirm");
        $this->post("/sales-orders/{$so->id}/start-picking");
        $this->post("/sales-orders/{$so->id}/dispatch");

        return $so->fresh();
    }

    public function test_dispatch_auto_generates_invoice_with_batch_and_expiry_lines(): void
    {
        $this->actingAsAuthenticatedUser();
        $so = $this->createDispatchedSalesOrder('PRD-INV-1', 10, 5.00);

        $this->assertSame(SalesOrder::STATUS_INVOICED, $so->status);

        $invoice = $so->invoice;
        $this->assertNotNull($invoice);
        $this->assertSame(50.00, (float) $invoice->total);

        $item = $invoice->items()->first();
        $this->assertSame('B-INV-1', $item->batch_number);
        $this->assertNotNull($item->expiry_date);
    }

    public function test_full_payment_settles_invoice_and_completes_sales_order(): void
    {
        $this->actingAsAuthenticatedUser();
        $so = $this->createDispatchedSalesOrder('PRD-INV-2', 10, 5.00);
        $invoice = $so->invoice;
        $client = $so->client;

        $this->post("/clients/{$client->id}/payments", [
            'amount' => 50.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'eft',
            'reference' => 'EFT-001',
            'allocations' => [
                ['sales_invoice_id' => $invoice->id, 'amount' => 50.00],
            ],
        ])->assertRedirect();

        $invoice->refresh();
        $this->assertSame(SalesInvoice::STATUS_PAID, $invoice->status);
        $this->assertTrue($invoice->isSettled());
        $this->assertSame(SalesOrder::STATUS_COMPLETED, $so->fresh()->status);
    }

    public function test_partial_payment_marks_invoice_partially_paid(): void
    {
        $this->actingAsAuthenticatedUser();
        $so = $this->createDispatchedSalesOrder('PRD-INV-3', 10, 5.00);
        $invoice = $so->invoice;
        $client = $so->client;

        $this->post("/clients/{$client->id}/payments", [
            'amount' => 20.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'allocations' => [
                ['sales_invoice_id' => $invoice->id, 'amount' => 20.00],
            ],
        ])->assertRedirect();

        $invoice->refresh();
        $this->assertSame(SalesInvoice::STATUS_PARTIALLY_PAID, $invoice->status);
        $this->assertSame(30.00, $invoice->balance());
        $this->assertSame(SalesOrder::STATUS_INVOICED, $so->fresh()->status);
    }

    public function test_credit_note_reduces_balance_and_can_settle_invoice(): void
    {
        $this->actingAsAuthenticatedUser();
        $so = $this->createDispatchedSalesOrder('PRD-INV-4', 10, 5.00);
        $invoice = $so->invoice;

        $this->post("/sales-invoices/{$invoice->id}/credit-notes", [
            'amount' => 50.00,
            'reason' => 'Pricing correction',
        ])->assertRedirect();

        $invoice->refresh();
        $this->assertSame(0.0, $invoice->balance());
        $this->assertSame(SalesInvoice::STATUS_PAID, $invoice->status);
    }

    public function test_customer_statement_shows_correct_ageing_and_balance(): void
    {
        $this->actingAsAuthenticatedUser();
        $so = $this->createDispatchedSalesOrder('PRD-INV-5', 10, 5.00);
        $invoice = $so->invoice;
        $invoice->update(['due_date' => now()->subDays(45)->toDateString()]);

        $response = $this->get("/clients/{$so->client_id}/statement");

        $response->assertOk();
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('50.00');
    }
}
