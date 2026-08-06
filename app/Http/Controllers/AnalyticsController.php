<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\PurchaseOrder;
use App\Models\SalesCreditNote;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        $now      = Carbon::now();
        $start    = $now->copy()->startOfMonth();
        $end      = $now->copy()->endOfMonth();
        $lastS    = $now->copy()->subMonth()->startOfMonth();
        $lastE    = $now->copy()->subMonth()->endOfMonth();

        // ── Revenue last 30 days (invoiced totals) ──
        // Converted to USD-equivalent per invoice (see ExchangeRate) since invoices can
        // be raised in USD or ZWG — a raw SQL SUM() would silently mix the two currencies.
        $notCancelled = fn ($query) => $query->where('status', '!=', SalesInvoice::STATUS_CANCELLED);

        $recentInvoices = $notCancelled(SalesInvoice::where('invoice_date', '>=', $now->copy()->subDays(29)->startOfDay()))
            ->get(['id', 'sales_order_id', 'invoice_date', 'total']);
        $recentInvoices->loadMissing('salesOrder');
        $revenueByDay = $recentInvoices->groupBy(fn (SalesInvoice $i) => $i->invoice_date->toDateString())
            ->map(fn ($group) => $group->sum(fn (SalesInvoice $i) => $i->totalInUsd()));

        $revenueLabels = [];
        $revenueData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            $revenueLabels[] = Carbon::parse($d)->format('d M');
            $revenueData[]   = (float) ($revenueByDay[$d] ?? 0);
        }

        // ── Revenue this/last month (cancelled invoices never count as revenue) ──
        $thisMonthInvoices = $notCancelled(SalesInvoice::whereBetween('invoice_date', [$start, $end]))->get();
        $lastMonthInvoices = $notCancelled(SalesInvoice::whereBetween('invoice_date', [$lastS, $lastE]))->get();

        $revenueThisMonth = (float) $thisMonthInvoices->sum(fn (SalesInvoice $i) => $i->totalInUsd());
        $revenueLastMonth = (float) $lastMonthInvoices->sum(fn (SalesInvoice $i) => $i->totalInUsd());
        $revDiff = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : null;

        // ── Invoice counts ──
        $invoiceCountThis = $thisMonthInvoices->count();
        $invoiceCountAll  = $notCancelled(SalesInvoice::query())->count();
        $invoicesWithValue = $notCancelled(SalesInvoice::where('total', '>', 0))->get();
        $avgInvoiceValue  = $invoicesWithValue->isNotEmpty()
            ? (float) $invoicesWithValue->avg(fn (SalesInvoice $i) => $i->totalInUsd())
            : 0.0;

        // ── Outstanding accounts receivable & ageing ──
        $openInvoices = SalesInvoice::whereIn('status', [
            SalesInvoice::STATUS_UNPAID,
            SalesInvoice::STATUS_PARTIALLY_PAID,
        ])->with(['creditNotes', 'paymentAllocations'])->get();

        $ageing = ['current' => 0.0, '30' => 0.0, '60' => 0.0, '90+' => 0.0];
        foreach ($openInvoices as $invoice) {
            $ageing[$invoice->ageingBucket()] += $invoice->balanceInUsd();
        }
        $totalOutstanding = round(array_sum($ageing), 2);

        // ── 6-month invoiced-vs-credited ──
        $months6 = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months6->push($now->copy()->subMonths($i)->format('Y-m'));
        }
        $monthly6Labels = $months6->map(fn ($m) => Carbon::parse($m . '-01')->format('M Y'))->toArray();

        // Converted to USD-equivalent per record — a raw SQL SUM() would mix currencies.
        $monthlyInvoiced = $notCancelled(SalesInvoice::where('invoice_date', '>=', $now->copy()->subMonths(5)->startOfMonth()))
            ->get()
            ->groupBy(fn (SalesInvoice $i) => $i->invoice_date->format('Y-m'))
            ->map(fn ($group) => $group->sum(fn (SalesInvoice $i) => $i->totalInUsd()));

        $monthlyCredited = SalesCreditNote::with('salesInvoice')
            ->where('created_at', '>=', $now->copy()->subMonths(5)->startOfMonth())
            ->get()
            ->groupBy(fn (SalesCreditNote $c) => $c->created_at->format('Y-m'))
            ->map(fn ($group) => $group->sum(fn (SalesCreditNote $c) => $c->amountInUsd()));

        $invoicedByMonth = $months6->map(fn ($m) => (float) ($monthlyInvoiced[$m] ?? 0))->toArray();
        $creditedByMonth = $months6->map(fn ($m) => (float) ($monthlyCredited[$m] ?? 0))->toArray();

        // ── Sales order pipeline ──
        $soPipeline = SalesOrder::selectRaw('status, COUNT(*) as cnt')->groupBy('status')->pluck('cnt', 'status');
        $soStatuses = [
            SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_CONFIRMED, SalesOrder::STATUS_PICKING,
            SalesOrder::STATUS_DISPATCHED, SalesOrder::STATUS_INVOICED, SalesOrder::STATUS_COMPLETED,
        ];

        // ── Purchase order status breakdown ──
        $poStatuses = PurchaseOrder::selectRaw('status, COUNT(*) as cnt')->groupBy('status')->pluck('cnt', 'status');

        // ── Top 10 products by qty invoiced ──
        // Revenue converted to USD-equivalent per invoice's currency before grouping.
        $topProducts = $notCancelled(SalesInvoice::query())
            ->with('items')
            ->get()
            ->flatMap(fn (SalesInvoice $invoice) => $invoice->items->map(fn ($item) => [
                'product_code' => $item->product_code,
                'product_description' => $item->product_description,
                'qty' => $item->qty,
                'revenue' => ExchangeRate::toUsd((float) $item->line_total, $invoice->currency()),
            ]))
            ->groupBy('product_code')
            ->map(fn ($group) => (object) [
                'product_code' => $group->first()['product_code'],
                'product_description' => $group->first()['product_description'],
                'qty_sold' => $group->sum('qty'),
                'revenue' => $group->sum('revenue'),
            ])
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        // ── Top clients by revenue ──
        $topClients = $notCancelled(SalesInvoice::query())
            ->with('client')
            ->get()
            ->groupBy('client_id')
            ->map(fn ($group) => (object) [
                'name' => $group->first()->client?->name,
                'cnt' => $group->count(),
                'total' => $group->sum(fn (SalesInvoice $i) => $i->totalInUsd()),
            ])
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // ── Low stock / restock recommendation ──
        $lowStock = Stock::whereColumn('quantity', '<=', 'reorder_point')
            ->where('reorder_point', '>', 0)
            ->orderBy('quantity')
            ->limit(10)
            ->get(['product_code', 'product_description', 'quantity', 'reorder_point', 'reorder_qty', 'selling_price']);

        // ── Expiring batches ──
        $expiringSoon = DB::table('stock_batches as b')
            ->join('stocks as s', 's.product_code', '=', 'b.product_code')
            ->where('b.status', 'active')
            ->whereDate('b.expiry_date', '>=', $now->toDateString())
            ->whereDate('b.expiry_date', '<=', $now->copy()->addDays(90)->toDateString())
            ->orderBy('b.expiry_date')
            ->limit(10)
            ->get([
                'b.product_code',
                's.product_description',
                'b.batch_number',
                'b.expiry_date',
                'b.qty_on_hand',
            ]);

        // ── Stock value ──
        $stockValue = DB::table('stocks')
            ->selectRaw('SUM(selling_price * quantity) as retail, SUM(buying_price * quantity) as cost')
            ->first();

        // ── Expenses this month ──
        $expensesThisMonth = DB::table('expenses')->whereBetween('created_at', [$start, $end])->sum('amount');

        return view('analytics.index', compact(
            'revenueLabels', 'revenueData',
            'revenueThisMonth', 'revenueLastMonth', 'revDiff',
            'invoiceCountThis', 'invoiceCountAll', 'avgInvoiceValue',
            'ageing', 'totalOutstanding',
            'monthly6Labels', 'invoicedByMonth', 'creditedByMonth',
            'soPipeline', 'soStatuses', 'poStatuses',
            'topProducts', 'topClients',
            'lowStock', 'expiringSoon', 'stockValue',
            'expensesThisMonth'
        ));
    }
}
