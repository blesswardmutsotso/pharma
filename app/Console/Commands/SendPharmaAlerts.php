<?php

namespace App\Console\Commands;

use App\Mail\PharmaAlertDigest;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPharmaAlerts extends Command
{
    protected $signature = 'pharma:send-alerts';

    protected $description = 'Email the daily low-stock / expiry / overdue PO / overdue invoice digest to relevant roles';

    public function handle(): int
    {
        $lowStock = Stock::whereColumn('quantity', '<=', 'reorder_point')->where('reorder_point', '>', 0)
            ->orderBy('quantity')->get()->map(fn (Stock $s) => [
                'code' => $s->product_code,
                'name' => $s->product_description,
                'qty' => $s->quantity,
                'reorder_point' => $s->reorder_point,
            ])->all();

        $expiringBatches = StockBatch::with('stock')->active()->expiringWithin(90)->orderBy('expiry_date')->get()
            ->map(fn (StockBatch $b) => [
                'code' => $b->product_code,
                'batch' => $b->batch_number,
                'expiry' => $b->expiry_date->format('Y-m-d'),
                'qty' => $b->qty_on_hand,
            ])->all();

        $overduePurchaseOrders = PurchaseOrder::with('supplier')
            ->whereIn('status', [PurchaseOrder::STATUS_APPROVED])
            ->whereNotNull('expected_delivery_date')
            ->where('expected_delivery_date', '<', now()->startOfDay())
            ->get()
            ->map(fn (PurchaseOrder $po) => [
                'number' => $po->po_number,
                'supplier' => $po->supplier?->name,
                'expected' => $po->expected_delivery_date->format('Y-m-d'),
                'days_overdue' => (int) $po->expected_delivery_date->diffInDays(now()),
            ])->all();

        $overdueInvoices = SalesInvoice::with('client')
            ->whereIn('status', [SalesInvoice::STATUS_UNPAID, SalesInvoice::STATUS_PARTIALLY_PAID])
            ->get()
            ->filter(fn (SalesInvoice $invoice) => $invoice->daysOverdue() > 0)
            ->map(fn (SalesInvoice $invoice) => [
                'number' => $invoice->invoice_number,
                'client' => $invoice->client?->name,
                'due' => $invoice->due_date?->format('Y-m-d'),
                'balance' => number_format($invoice->balance(), 2),
                'days_overdue' => $invoice->daysOverdue(),
            ])->values()->all();

        if (!$lowStock && !$expiringBatches && !$overduePurchaseOrders && !$overdueInvoices) {
            $this->info('Nothing to report today — no alerts sent.');
            return self::SUCCESS;
        }

        $recipients = User::whereIn('role', [
            User::ROLE_ADMIN,
            User::ROLE_INVENTORY_MANAGER,
            User::ROLE_PROCUREMENT,
            User::ROLE_FINANCE,
            User::ROLE_WAREHOUSE,
        ])->where('is_active', true)->get();

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->send(new PharmaAlertDigest(
                $lowStock,
                $expiringBatches,
                $overduePurchaseOrders,
                $overdueInvoices,
            ));
        }

        $this->info("Alert digest sent to {$recipients->count()} recipient(s).");

        return self::SUCCESS;
    }
}
