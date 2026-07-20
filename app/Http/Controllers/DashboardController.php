<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Stock;
use App\Models\StockBatch;

class DashboardController extends Controller
{
    public function index()
    {
        $lowStockCount = Stock::whereColumn('quantity', '<=', 'reorder_point')
            ->where('reorder_point', '>', 0)
            ->count();

        $expiringBatchesCount = StockBatch::active()->expiringWithin(90)->count();

        $openPurchaseOrders = PurchaseOrder::whereIn('status', [
            PurchaseOrder::STATUS_DRAFT,
            PurchaseOrder::STATUS_SUBMITTED,
            PurchaseOrder::STATUS_APPROVED,
        ])->count();

        $openSalesOrders = SalesOrder::whereIn('status', [
            SalesOrder::STATUS_DRAFT,
            SalesOrder::STATUS_CONFIRMED,
            SalesOrder::STATUS_PICKING,
        ])->count();

        $unpaidInvoicesTotal = (float) SalesInvoice::whereIn('status', [
            SalesInvoice::STATUS_UNPAID,
            SalesInvoice::STATUS_PARTIALLY_PAID,
        ])->get()->sum(fn (SalesInvoice $invoice) => $invoice->balance());

        $recentSalesOrders = SalesOrder::with('client')->latest()->limit(6)->get();
        $recentPurchaseOrders = PurchaseOrder::with('supplier')->latest()->limit(6)->get();

        $lowStockItems = Stock::whereColumn('quantity', '<=', 'reorder_point')
            ->where('reorder_point', '>', 0)
            ->orderBy('quantity')
            ->limit(8)
            ->get();

        $expiringBatches = StockBatch::with('stock')
            ->active()
            ->expiringWithin(90)
            ->orderBy('expiry_date')
            ->limit(8)
            ->get();

        return view('dashboard', compact(
            'lowStockCount',
            'expiringBatchesCount',
            'openPurchaseOrders',
            'openSalesOrders',
            'unpaidInvoicesTotal',
            'recentSalesOrders',
            'recentPurchaseOrders',
            'lowStockItems',
            'expiringBatches',
        ));
    }
}
