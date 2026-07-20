<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\StockAuditLog;
use App\Models\StockBatch;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        // Every pharma role may run reports (read-only); no write actions here.
        return [new Middleware('role:admin,inventory_manager,sales,procurement,finance,warehouse,auditor')];
    }

    public function index()
    {
        $reports = [
            ['slug' => 'current-stock',       'name' => 'Current Stock Report',      'icon' => 'bi-box-seam',           'desc' => 'SKU, batch, expiry, quantity on hand and value.'],
            ['slug' => 'stock-movement',      'name' => 'Stock Movement Report',     'icon' => 'bi-arrow-left-right',   'desc' => 'Every stock-affecting transaction with before/after quantities.'],
            ['slug' => 'expiry-alert',        'name' => 'Expiry Alert Report',       'icon' => 'bi-hourglass-split',    'desc' => 'Batches expiring within 90 days.'],
            ['slug' => 'low-stock',           'name' => 'Low Stock Report',          'icon' => 'bi-exclamation-triangle', 'desc' => 'Products at or below their reorder point.'],
            ['slug' => 'stock-valuation',     'name' => 'Stock Valuation Report',    'icon' => 'bi-cash-stack',         'desc' => 'Cost and retail value of stock on hand, by category.'],
            ['slug' => 'sales-summary',       'name' => 'Sales Summary Report',      'icon' => 'bi-graph-up',           'desc' => 'Daily invoiced revenue for a date range.'],
            ['slug' => 'sales-order-status',  'name' => 'Sales Order Status Report', 'icon' => 'bi-cart-check',         'desc' => 'Every sales order, its value, and pipeline status.'],
            ['slug' => 'purchase-order-report', 'name' => 'Purchase Order Report',   'icon' => 'bi-file-earmark-text', 'desc' => 'Every purchase order, ordered vs received quantities.'],
            ['slug' => 'supplier-performance', 'name' => 'Supplier Performance Report', 'icon' => 'bi-truck',           'desc' => 'Fill rate and discrepancy rate by supplier.'],
            ['slug' => 'debtors-ageing',       'name' => 'Debtors Ageing Report',     'icon' => 'bi-calendar-week',     'desc' => 'Outstanding balances by client and ageing bucket.'],
            ['slug' => 'revenue-report',       'name' => 'Revenue Report',           'icon' => 'bi-bar-chart-line',    'desc' => 'Invoiced vs credited revenue by month.'],
            ['slug' => 'batch-traceability',   'name' => 'Batch Traceability Report', 'icon' => 'bi-upc-scan',          'desc' => 'Full batch history: received, allocated, dispatched.'],
            ['slug' => 'batch-recall',         'name' => 'Batch Recall Report',       'icon' => 'bi-exclamation-octagon', 'desc' => 'Every client invoiced a given batch — for recalls and complaints.'],
        ];

        return view('reports.index', compact('reports'));
    }

    /**
     * Single named route (reports.show) dispatches to the matching report
     * builder method below by slug, so every report shares one URL shape.
     */
    public function show(Request $request, string $report)
    {
        $map = [
            'current-stock' => 'currentStock',
            'stock-movement' => 'stockMovement',
            'expiry-alert' => 'expiryAlert',
            'low-stock' => 'lowStock',
            'stock-valuation' => 'stockValuation',
            'sales-summary' => 'salesSummary',
            'sales-order-status' => 'salesOrderStatus',
            'purchase-order-report' => 'purchaseOrderReport',
            'supplier-performance' => 'supplierPerformance',
            'debtors-ageing' => 'debtorsAgeing',
            'revenue-report' => 'revenueReport',
            'batch-traceability' => 'batchTraceability',
            'batch-recall' => 'batchRecall',
        ];

        abort_unless(isset($map[$report]), 404);

        return $this->{$map[$report]}($request);
    }

    public function currentStock(Request $request)
    {
        $rows = Stock::orderBy('product_description')->get()->map(fn (Stock $s) => [
            'code' => $s->product_code,
            'name' => $s->product_description,
            'category' => $s->category,
            'qty' => $s->quantity,
            'reorder_point' => $s->reorder_point,
            'unit_price' => number_format($s->selling_price, 2),
            'value' => number_format($s->quantity * $s->selling_price, 2),
        ]);

        return $this->renderReport($request, 'current-stock', 'Current Stock Report', [
            'code' => 'SKU', 'name' => 'Product', 'category' => 'Category', 'qty' => 'Qty on Hand',
            'reorder_point' => 'Reorder Point', 'unit_price' => 'Unit Price', 'value' => 'Stock Value',
        ], $rows);
    }

    public function stockMovement(Request $request)
    {
        $rows = StockAuditLog::latest()->limit(300)->get()->map(fn (StockAuditLog $log) => [
            'date' => $log->created_at->format('Y-m-d H:i'),
            'action' => $log->actionLabel(),
            'code' => $log->product_code,
            'name' => $log->product_description,
            'qty_before' => $log->qty_before,
            'qty_after' => $log->qty_after,
            'qty_change' => $log->qty_change,
            'reference' => $log->reference_label,
            'by' => $log->performed_by_name,
        ]);

        return $this->renderReport($request, 'stock-movement', 'Stock Movement Report', [
            'date' => 'Date', 'action' => 'Action', 'code' => 'SKU', 'name' => 'Product',
            'qty_before' => 'Qty Before', 'qty_after' => 'Qty After', 'qty_change' => 'Change',
            'reference' => 'Reference', 'by' => 'Performed By',
        ], $rows);
    }

    public function expiryAlert(Request $request)
    {
        $days = (int) $request->input('days', 90);

        $rows = StockBatch::with('stock')->active()->expiringWithin($days)->orderBy('expiry_date')->get()
            ->map(fn (StockBatch $b) => [
                'code' => $b->product_code,
                'name' => $b->stock?->product_description,
                'batch' => $b->batch_number,
                'expiry' => $b->expiry_date->format('Y-m-d'),
                'days_left' => now()->diffInDays($b->expiry_date, false),
                'qty' => $b->qty_on_hand,
            ]);

        return $this->renderReport($request, 'expiry-alert', "Expiry Alert Report (next {$days} days)", [
            'code' => 'SKU', 'name' => 'Product', 'batch' => 'Batch', 'expiry' => 'Expiry Date',
            'days_left' => 'Days Left', 'qty' => 'Qty',
        ], $rows);
    }

    public function lowStock(Request $request)
    {
        $rows = Stock::whereColumn('quantity', '<=', 'reorder_point')->where('reorder_point', '>', 0)
            ->orderBy('quantity')->get()->map(fn (Stock $s) => [
                'code' => $s->product_code,
                'name' => $s->product_description,
                'qty' => $s->quantity,
                'reorder_point' => $s->reorder_point,
                'reorder_qty' => $s->reorder_qty,
                'supplier' => $s->defaultSupplier?->name,
            ]);

        return $this->renderReport($request, 'low-stock', 'Low Stock Report', [
            'code' => 'SKU', 'name' => 'Product', 'qty' => 'Qty on Hand', 'reorder_point' => 'Reorder Point',
            'reorder_qty' => 'Suggested Order Qty', 'supplier' => 'Default Supplier',
        ], $rows);
    }

    public function stockValuation(Request $request)
    {
        $rows = Stock::orderBy('category')->orderBy('product_description')->get()->map(fn (Stock $s) => [
            'code' => $s->product_code,
            'name' => $s->product_description,
            'category' => $s->category ?? 'Uncategorised',
            'qty' => $s->quantity,
            'cost_value' => number_format($s->quantity * $s->buying_price, 2),
            'retail_value' => number_format($s->quantity * $s->selling_price, 2),
        ]);

        return $this->renderReport($request, 'stock-valuation', 'Stock Valuation Report', [
            'code' => 'SKU', 'name' => 'Product', 'category' => 'Category', 'qty' => 'Qty',
            'cost_value' => 'Cost Value', 'retail_value' => 'Retail Value',
        ], $rows);
    }

    public function salesSummary(Request $request)
    {
        $from = $request->input('from') ? Carbon::parse($request->input('from')) : now()->startOfMonth();
        $to   = $request->input('to') ? Carbon::parse($request->input('to')) : now()->endOfMonth();

        $rows = SalesInvoice::whereBetween('invoice_date', [$from, $to])
            ->where('status', '!=', SalesInvoice::STATUS_CANCELLED)
            ->selectRaw('invoice_date, COUNT(*) as cnt, SUM(subtotal) as subtotal, SUM(tax_total) as tax_total, SUM(total) as total')
            ->groupBy('invoice_date')->orderBy('invoice_date')->get()
            ->map(fn ($r) => [
                'date' => Carbon::parse($r->invoice_date)->format('Y-m-d'),
                'invoices' => $r->cnt,
                'subtotal' => number_format($r->subtotal, 2),
                'tax' => number_format($r->tax_total, 2),
                'total' => number_format($r->total, 2),
            ]);

        return $this->renderReport($request, 'sales-summary', 'Sales Summary Report (' . $from->format('Y-m-d') . ' to ' . $to->format('Y-m-d') . ')', [
            'date' => 'Date', 'invoices' => 'Invoices', 'subtotal' => 'Subtotal', 'tax' => 'Tax', 'total' => 'Total',
        ], $rows, ['from' => $from->toDateString(), 'to' => $to->toDateString()]);
    }

    public function salesOrderStatus(Request $request)
    {
        $rows = SalesOrder::with(['client', 'items'])->latest()->limit(300)->get()->map(fn (SalesOrder $so) => [
            'number' => $so->so_number,
            'client' => $so->client?->name,
            'date' => $so->order_date?->format('Y-m-d'),
            'status' => ucfirst($so->status),
            'value' => number_format($so->items->sum('line_total'), 2),
        ]);

        return $this->renderReport($request, 'sales-order-status', 'Sales Order Status Report', [
            'number' => 'SO Number', 'client' => 'Client', 'date' => 'Order Date', 'status' => 'Status', 'value' => 'Value',
        ], $rows);
    }

    public function purchaseOrderReport(Request $request)
    {
        $rows = PurchaseOrder::with(['supplier', 'items'])->latest()->limit(300)->get()->map(fn (PurchaseOrder $po) => [
            'number' => $po->po_number,
            'supplier' => $po->supplier?->name,
            'date' => $po->order_date?->format('Y-m-d'),
            'status' => ucfirst($po->status),
            'ordered' => $po->items->sum('qty_ordered'),
            'received' => $po->items->sum('qty_received'),
        ]);

        return $this->renderReport($request, 'purchase-order-report', 'Purchase Order Report', [
            'number' => 'PO Number', 'supplier' => 'Supplier', 'date' => 'Order Date', 'status' => 'Status',
            'ordered' => 'Qty Ordered', 'received' => 'Qty Received',
        ], $rows);
    }

    public function supplierPerformance(Request $request)
    {
        $rows = Supplier::all()->map(function (Supplier $supplier) {
            $items = PurchaseOrderItem::whereHas('purchaseOrder', fn ($q) => $q->where('supplier_id', $supplier->id))->get();
            $ordered = $items->sum('qty_ordered');
            $received = $items->sum('qty_received');
            $discrepant = $items->filter(fn ($i) => $i->qty_received != $i->qty_ordered)->count();

            return [
                'name' => $supplier->name,
                'pos' => $supplier->purchaseOrders()->count(),
                'fill_rate' => $ordered > 0 ? number_format(($received / $ordered) * 100, 1) . '%' : '—',
                'discrepancy_rate' => $items->count() > 0 ? number_format(($discrepant / $items->count()) * 100, 1) . '%' : '—',
            ];
        });

        return $this->renderReport($request, 'supplier-performance', 'Supplier Performance Report', [
            'name' => 'Supplier', 'pos' => 'Purchase Orders', 'fill_rate' => 'Fill Rate', 'discrepancy_rate' => 'Discrepancy Rate',
        ], $rows);
    }

    public function debtorsAgeing(Request $request)
    {
        $invoices = SalesInvoice::with('client')->whereIn('status', [
            SalesInvoice::STATUS_UNPAID, SalesInvoice::STATUS_PARTIALLY_PAID,
        ])->get();

        $byClient = $invoices->groupBy('client_id')->map(function ($group) {
            $client = $group->first()->client;
            $buckets = ['current' => 0.0, '30' => 0.0, '60' => 0.0, '90+' => 0.0];
            foreach ($group as $invoice) {
                $buckets[$invoice->ageingBucket()] += $invoice->balance();
            }

            return [
                'client' => $client?->name,
                'current' => number_format($buckets['current'], 2),
                'd30' => number_format($buckets['30'], 2),
                'd60' => number_format($buckets['60'], 2),
                'd90' => number_format($buckets['90+'], 2),
                'total' => number_format(array_sum($buckets), 2),
            ];
        })->values();

        return $this->renderReport($request, 'debtors-ageing', 'Debtors Ageing Report', [
            'client' => 'Client', 'current' => 'Current', 'd30' => '30 Days', 'd60' => '60 Days',
            'd90' => '90+ Days', 'total' => 'Total Balance',
        ], $byClient);
    }

    public function revenueReport(Request $request)
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->copy()->subMonths($i)->format('Y-m'));
        }

        $isSqlite = DB::getDriverName() === 'sqlite';
        $invoiceExpr = $isSqlite ? "strftime('%Y-%m', invoice_date)" : "DATE_FORMAT(invoice_date, '%Y-%m')";
        $creditExpr  = $isSqlite ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";

        $invoiced = SalesInvoice::where('status', '!=', SalesInvoice::STATUS_CANCELLED)
            ->selectRaw("{$invoiceExpr} as month, SUM(total) as total")
            ->groupByRaw($invoiceExpr)->pluck('total', 'month');
        $credited = DB::table('sales_credit_notes')
            ->selectRaw("{$creditExpr} as month, SUM(amount) as total")
            ->groupByRaw($creditExpr)->pluck('total', 'month');

        $rows = $months->map(fn ($m) => [
            'month' => Carbon::parse($m . '-01')->format('M Y'),
            'invoiced' => number_format($invoiced[$m] ?? 0, 2),
            'credited' => number_format($credited[$m] ?? 0, 2),
            'net' => number_format(($invoiced[$m] ?? 0) - ($credited[$m] ?? 0), 2),
        ]);

        return $this->renderReport($request, 'revenue-report', 'Revenue Report (last 12 months)', [
            'month' => 'Month', 'invoiced' => 'Invoiced', 'credited' => 'Credited', 'net' => 'Net Revenue',
        ], $rows);
    }

    public function batchTraceability(Request $request)
    {
        $rows = StockBatch::with(['stock', 'itemAllocations.salesOrderItem.salesOrder'])
            ->orderBy('product_code')->orderBy('expiry_date')->limit(300)->get()
            ->map(function (StockBatch $batch) {
                $soNumbers = $batch->itemAllocations
                    ->map(fn ($a) => $a->salesOrderItem?->salesOrder?->so_number)
                    ->filter()->unique()->implode(', ');

                return [
                    'code' => $batch->product_code,
                    'name' => $batch->stock?->product_description,
                    'batch' => $batch->batch_number,
                    'expiry' => $batch->expiry_date->format('Y-m-d'),
                    'source' => $batch->source_type . ($batch->source_id ? " #{$batch->source_id}" : ''),
                    'qty_on_hand' => $batch->qty_on_hand,
                    'status' => ucfirst($batch->status),
                    'sold_to_orders' => $soNumbers ?: '—',
                ];
            });

        return $this->renderReport($request, 'batch-traceability', 'Batch Traceability Report', [
            'code' => 'SKU', 'name' => 'Product', 'batch' => 'Batch', 'expiry' => 'Expiry Date',
            'source' => 'Source', 'qty_on_hand' => 'Qty on Hand', 'status' => 'Status', 'sold_to_orders' => 'Sold To (Sales Orders)',
        ], $rows);
    }

    /**
     * BRD-adjacent compliance report: given a batch number, find every
     * client who was invoiced stock from that batch — the basic query a
     * pharma wholesaler needs to run a recall or investigate a complaint.
     */
    public function batchRecall(Request $request)
    {
        $batchNumber = trim((string) $request->input('batch_number', ''));

        $rows = collect();

        if ($batchNumber !== '') {
            $rows = SalesInvoiceItem::with('salesInvoice.client')
                ->where('batch_number', $batchNumber)
                ->get()
                ->map(fn (SalesInvoiceItem $item) => [
                    'code' => $item->product_code,
                    'name' => $item->product_description,
                    'batch' => $item->batch_number,
                    'expiry' => $item->expiry_date?->format('Y-m-d'),
                    'client' => $item->salesInvoice?->client?->name,
                    'invoice' => $item->salesInvoice?->invoice_number,
                    'invoice_date' => $item->salesInvoice?->invoice_date?->format('Y-m-d'),
                    'qty' => $item->qty,
                ]);
        }

        $title = $batchNumber !== '' ? "Batch Recall Report — Batch {$batchNumber}" : 'Batch Recall Report';

        return $this->renderReport($request, 'batch-recall', $title, [
            'code' => 'SKU', 'name' => 'Product', 'batch' => 'Batch', 'expiry' => 'Expiry Date',
            'client' => 'Client', 'invoice' => 'Invoice No.', 'invoice_date' => 'Invoice Date', 'qty' => 'Qty Supplied',
        ], $rows, ['batch_number' => $batchNumber]);
    }

    private function renderReport(Request $request, string $slug, string $title, array $columns, $rows, array $filters = [])
    {
        if ($request->boolean('export')) {
            return $this->streamCsv($slug, $columns, $rows);
        }

        return view('reports.show', compact('slug', 'title', 'columns', 'rows', 'filters'));
    }

    private function streamCsv(string $slug, array $columns, $rows)
    {
        $filename = $slug . '-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($columns, $rows) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, array_values($columns));
            foreach ($rows as $row) {
                $row = (array) $row;
                fputcsv($fh, array_map(fn ($key) => $row[$key] ?? '', array_keys($columns)));
            }
            fclose($fh);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
