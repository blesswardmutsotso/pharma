<?php

namespace Tests\Feature\Pharma;

use App\Models\Client;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchRecallAndRegulatoryFieldsTest extends TestCase
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

    public function test_product_can_be_created_with_regulatory_fields(): void
    {
        $this->actingAsAuthenticatedUser();

        $response = $this->post('/products', [
            'product_code' => 'REG-1',
            'product_description' => 'Codeine Linctus',
            'manufacturer' => 'Acme Pharmaceuticals',
            'registration_number' => 'MCAZ-2026-00123',
            'controlled_substance_schedule' => 'Schedule III',
            'buying_price' => 2,
            'selling_price' => 4,
        ]);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('stocks', [
            'product_code' => 'REG-1',
            'manufacturer' => 'Acme Pharmaceuticals',
            'registration_number' => 'MCAZ-2026-00123',
            'controlled_substance_schedule' => 'Schedule III',
        ]);
    }

    public function test_batch_recall_report_finds_every_client_invoiced_a_given_batch(): void
    {
        $this->actingAsAuthenticatedUser();

        Stock::factory()->create(['product_code' => 'RECALL-1', 'quantity' => 0]);
        $clientA = Client::create(['name' => 'Recall Client A']);
        $clientB = Client::create(['name' => 'Recall Client B']);

        $soA = SalesOrder::create([
            'so_number' => 'SO-RECALL-A',
            'client_id' => $clientA->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_INVOICED,
        ]);

        $invoiceA = SalesInvoice::create([
            'invoice_number' => 'INV-RECALL-A',
            'sales_order_id' => $soA->id,
            'client_id' => $clientA->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => SalesInvoice::STATUS_UNPAID,
            'subtotal' => 100, 'tax_total' => 0, 'total' => 100,
        ]);
        SalesInvoiceItem::create([
            'sales_invoice_id' => $invoiceA->id,
            'product_code' => 'RECALL-1',
            'product_description' => 'Recalled Product',
            'batch_number' => 'RECALLED-BATCH',
            'expiry_date' => now()->addMonths(6),
            'qty' => 5,
            'unit_price' => 20,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'line_total' => 100,
        ]);

        $soB = SalesOrder::create([
            'so_number' => 'SO-RECALL-B',
            'client_id' => $clientB->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_INVOICED,
        ]);

        $invoiceB = SalesInvoice::create([
            'invoice_number' => 'INV-RECALL-B',
            'sales_order_id' => $soB->id,
            'client_id' => $clientB->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => SalesInvoice::STATUS_UNPAID,
            'subtotal' => 40, 'tax_total' => 0, 'total' => 40,
        ]);
        SalesInvoiceItem::create([
            'sales_invoice_id' => $invoiceB->id,
            'product_code' => 'RECALL-1',
            'product_description' => 'Recalled Product',
            'batch_number' => 'OTHER-BATCH',
            'expiry_date' => now()->addMonths(6),
            'qty' => 2,
            'unit_price' => 20,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'line_total' => 40,
        ]);

        $response = $this->get('/reports/batch-recall?batch_number=RECALLED-BATCH');

        $response->assertOk();
        $response->assertSeeText('Recall Client A');
        $response->assertDontSeeText('Recall Client B');
    }
}
