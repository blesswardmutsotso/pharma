<?php

namespace Tests\Feature\Pharma;

use App\Mail\PharmaAlertDigest;
use App\Models\Client;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PharmaAlertDigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_digest_is_sent_to_relevant_roles_and_contains_expected_data(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);
        $inventoryManager = User::factory()->create(['role' => User::ROLE_INVENTORY_MANAGER, 'is_active' => true]);
        $salesUser = User::factory()->create(['role' => User::ROLE_SALES, 'is_active' => true]);
        $inactiveAdmin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => false]);

        // Low stock product.
        Stock::factory()->create(['product_code' => 'ALERT-LOW', 'quantity' => 2, 'reorder_point' => 10]);

        // Expiring batch.
        Stock::factory()->create(['product_code' => 'ALERT-EXP', 'quantity' => 5]);
        StockBatch::create([
            'product_code' => 'ALERT-EXP',
            'batch_number' => 'EXP-SOON',
            'expiry_date' => now()->addDays(30),
            'qty_on_hand' => 5,
            'unit_cost' => 1,
            'status' => StockBatch::STATUS_ACTIVE,
        ]);

        // Overdue PO.
        $supplier = Supplier::factory()->create(['status' => 'active']);
        PurchaseOrder::create([
            'po_number' => 'PO-OVERDUE-1',
            'supplier_id' => $supplier->id,
            'order_date' => now()->subDays(20),
            'expected_delivery_date' => now()->subDays(5),
            'status' => PurchaseOrder::STATUS_APPROVED,
        ]);

        // Overdue invoice.
        $client = Client::create(['name' => 'Overdue Client']);
        $so = SalesOrder::create([
            'so_number' => 'SO-OVERDUE-1',
            'client_id' => $client->id,
            'order_date' => now()->subDays(40),
            'status' => SalesOrder::STATUS_INVOICED,
        ]);
        SalesInvoice::create([
            'invoice_number' => 'INV-OVERDUE-1',
            'sales_order_id' => $so->id,
            'client_id' => $client->id,
            'invoice_date' => now()->subDays(40),
            'due_date' => now()->subDays(10),
            'status' => SalesInvoice::STATUS_UNPAID,
            'subtotal' => 50, 'tax_total' => 0, 'total' => 50,
        ]);

        $this->artisan('pharma:send-alerts')->assertExitCode(0);

        Mail::assertSent(PharmaAlertDigest::class, 2);
        Mail::assertSent(PharmaAlertDigest::class, fn ($mail) => $mail->hasTo($admin->email));
        Mail::assertSent(PharmaAlertDigest::class, fn ($mail) => $mail->hasTo($inventoryManager->email));
        Mail::assertNotSent(PharmaAlertDigest::class, fn ($mail) => $mail->hasTo($salesUser->email));
        Mail::assertNotSent(PharmaAlertDigest::class, fn ($mail) => $mail->hasTo($inactiveAdmin->email));

        Mail::assertSent(PharmaAlertDigest::class, function (PharmaAlertDigest $mail) {
            return collect($mail->lowStock)->contains(fn ($r) => $r['code'] === 'ALERT-LOW')
                && collect($mail->expiringBatches)->contains(fn ($r) => $r['batch'] === 'EXP-SOON')
                && collect($mail->overduePurchaseOrders)->contains(fn ($r) => $r['number'] === 'PO-OVERDUE-1')
                && collect($mail->overdueInvoices)->contains(fn ($r) => $r['number'] === 'INV-OVERDUE-1');
        });
    }

    public function test_no_mail_is_sent_when_there_is_nothing_to_report(): void
    {
        Mail::fake();

        User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $this->artisan('pharma:send-alerts')->assertExitCode(0);

        Mail::assertNothingSent();
    }
}
