<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
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
        $isSqlite = DB::getDriverName() === 'sqlite';

        // ── Revenue last 30 days (invoiced totals) ──
        $revenueDays = DB::table('sales_invoices')
            ->selectRaw('DATE(invoice_date) as day, SUM(total) as total')
            ->where('invoice_date', '>=', $now->copy()->subDays(29)->startOfDay())
            ->where('status', '!=', SalesInvoice::STATUS_CANCELLED)
            ->groupByRaw('DATE(invoice_date)')
            ->orderBy('day')
            ->get()->keyBy('day');

        $revenueLabels = [];
        $revenueData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            $revenueLabels[] = Carbon::parse($d)->format('d M');
            $revenueData[]   = (float) ($revenueDays[$d]->total ?? 0);
        }

        // ── Revenue this/last month (cancelled invoices never count as revenue) ──
        $notCancelled = fn ($query) => $query->where('status', '!=', SalesInvoice::STATUS_CANCELLED);

        $revenueThisMonth = (float) $notCancelled(SalesInvoice::whereBetween('invoice_date', [$start, $end]))->sum('total');
        $revenueLastMonth = (float) $notCancelled(SalesInvoice::whereBetween('invoice_date', [$lastS, $lastE]))->sum('total');
        $revDiff = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : null;

        // ── Invoice counts ──
        $invoiceCountThis = $notCancelled(SalesInvoice::whereBetween('invoice_date', [$start, $end]))->count();
        $invoiceCountAll  = $notCancelled(SalesInvoice::query())->count();
        $avgInvoiceValue  = (float) $notCancelled(SalesInvoice::where('total', '>', 0))->avg('total');

        // ── Outstanding accounts receivable & ageing ──
        $openInvoices = SalesInvoice::whereIn('status', [
            SalesInvoice::STATUS_UNPAID,
            SalesInvoice::STATUS_PARTIALLY_PAID,
        ])->with(['creditNotes', 'paymentAllocations'])->get();

        $ageing = ['current' => 0.0, '30' => 0.0, '60' => 0.0, '90+' => 0.0];
        foreach ($openInvoices as $invoice) {
            $ageing[$invoice->ageingBucket()] += $invoice->balance();
        }
        $totalOutstanding = round(array_sum($ageing), 2);

        // ── 6-month invoiced-vs-credited ──
        $months6 = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months6->push($now->copy()->subMonths($i)->format('Y-m'));
        }
        $monthly6Labels = $months6->map(fn ($m) => Carbon::parse($m . '-01')->format('M Y'))->toArray();

        $monthlyInvoiced = $isSqlite
            ? DB::table('sales_invoices')->selectRaw("strftime('%Y-%m', invoice_date) as month, SUM(total) as total")
                ->where('invoice_date', '>=', $now->copy()->subMonths(5)->startOfMonth())
                ->where('status', '!=', SalesInvoice::STATUS_CANCELLED)
                ->groupByRaw("strftime('%Y-%m', invoice_date)")->get()
            : DB::table('sales_invoices')->selectRaw("DATE_FORMAT(invoice_date,'%Y-%m') as month, SUM(total) as total")
                ->where('invoice_date', '>=', $now->copy()->subMonths(5)->startOfMonth())
                ->where('status', '!=', SalesInvoice::STATUS_CANCELLED)
                ->groupByRaw("DATE_FORMAT(invoice_date,'%Y-%m')")->get();

        $monthlyCredited = $isSqlite
            ? DB::table('sales_credit_notes')->selectRaw("strftime('%Y-%m', created_at) as month, SUM(amount) as total")
                ->where('created_at', '>=', $now->copy()->subMonths(5)->startOfMonth())
                ->groupByRaw("strftime('%Y-%m', created_at)")->get()
            : DB::table('sales_credit_notes')->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as month, SUM(amount) as total")
                ->where('created_at', '>=', $now->copy()->subMonths(5)->startOfMonth())
                ->groupByRaw("DATE_FORMAT(created_at,'%Y-%m')")->get();

        $invoicedByMonth = $months6->map(fn ($m) => (float) ($monthlyInvoiced->firstWhere('month', $m)->total ?? 0))->toArray();
        $creditedByMonth = $months6->map(fn ($m) => (float) ($monthlyCredited->firstWhere('month', $m)->total ?? 0))->toArray();

        // ── Sales order pipeline ──
        $soPipeline = SalesOrder::selectRaw('status, COUNT(*) as cnt')->groupBy('status')->pluck('cnt', 'status');
        $soStatuses = [
            SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_CONFIRMED, SalesOrder::STATUS_PICKING,
            SalesOrder::STATUS_DISPATCHED, SalesOrder::STATUS_INVOICED, SalesOrder::STATUS_COMPLETED,
        ];

        // ── Purchase order status breakdown ──
        $poStatuses = PurchaseOrder::selectRaw('status, COUNT(*) as cnt')->groupBy('status')->pluck('cnt', 'status');

        // ── Top 10 products by qty invoiced ──
        $topProducts = DB::table('sales_invoice_items as ii')
            ->join('sales_invoices as i', 'i.id', '=', 'ii.sales_invoice_id')
            ->where('i.status', '!=', SalesInvoice::STATUS_CANCELLED)
            ->selectRaw('ii.product_code, ii.product_description, SUM(ii.qty) as qty_sold, SUM(ii.line_total) as revenue')
            ->groupBy('ii.product_code', 'ii.product_description')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // ── Top clients by revenue ──
        $topClients = DB::table('sales_invoices as i')
            ->join('clients as c', 'c.id', '=', 'i.client_id')
            ->where('i.status', '!=', SalesInvoice::STATUS_CANCELLED)
            ->selectRaw('c.name, COUNT(*) as cnt, SUM(i.total) as total')
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

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
