<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,sales', only: ['create', 'store', 'convert']),
        ];
    }
    public function index()
    {
        $quotations = Quotation::with('client')->latest()->paginate(20);

        return view('quotations.index', compact('quotations'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->limit(200)->get();

        return view('quotations.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quote_number' => ['required', 'string', 'max:100', 'unique:quotations,quote_number'],
            'client_id' => ['required', 'exists:clients,id'],
            'quote_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_code' => ['required', 'string'],
            'items.*.product_description' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $quotation = Quotation::create([
                'quote_number' => $validated['quote_number'],
                'client_id' => $validated['client_id'],
                'quote_date' => $validated['quote_date'],
                'valid_until' => $validated['valid_until'] ?? null,
                'status' => Quotation::STATUS_DRAFT,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $discount = (float) ($item['discount'] ?? 0);
                $lineTotal = round(((float) $item['qty'] * (float) $item['unit_price']) - $discount, 2);

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_code' => $item['product_code'],
                    'product_description' => $item['product_description'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $discount,
                    'line_total' => $lineTotal,
                ]);
            }
        });

        return redirect()->route('quotations.index')->with('success', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['client', 'items', 'convertedSalesOrder']);

        return view('quotations.show', compact('quotation'));
    }

    public function pdf(Request $request, Quotation $quotation)
    {
        $quotation->load(['client', 'items']);

        $isDuplicate = $quotation->print_count > 0;
        $quotation->increment('print_count');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
            'isDuplicate' => $isDuplicate,
        ])->setPaper('a4', 'portrait');

        $filename = "{$quotation->quote_number}.pdf";

        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }

    public function convert(Quotation $quotation)
    {
        if (!$quotation->canBeConverted()) {
            return back()->with('error', 'This quotation cannot be converted.');
        }

        $salesOrder = DB::transaction(function () use ($quotation) {
            $salesOrder = SalesOrder::create([
                'so_number' => SalesOrder::generateSoNumber(),
                'client_id' => $quotation->client_id,
                'quotation_id' => $quotation->id,
                'order_date' => now()->toDateString(),
                'status' => SalesOrder::STATUS_DRAFT,
                'notes' => 'Converted from quotation ' . $quotation->quote_number,
                'created_by' => auth()->id(),
            ]);

            foreach ($quotation->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_code' => $item->product_code,
                    'product_description' => $item->product_description,
                    'qty_ordered' => $item->qty,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ]);
            }

            $quotation->markConverted($salesOrder);

            return $salesOrder;
        });

        return redirect()->route('sales-orders.show', $salesOrder)->with('success', 'Quotation converted to sales order.');
    }
}
