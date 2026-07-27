<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Concerns\Sortable;
use App\Models\SalesInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SalesInvoiceController extends Controller
{
    use ExportsCsv, Sortable;

    private const SORTABLE_COLUMNS = ['invoice_number', 'invoice_date', 'due_date', 'total', 'status'];

    private function filteredInvoicesQuery(Request $request)
    {
        $query = SalesInvoice::with('client');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->filteredInvoicesQuery($request);
        $invoices = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'invoice_date', 'desc')
            ->paginate(20)->withQueryString();

        return view('sales-invoices.index', compact('invoices'));
    }

    public function export(Request $request)
    {
        $query = $request->filled('ids')
            ? SalesInvoice::with('client')->whereIn('id', $request->input('ids'))
            : $this->filteredInvoicesQuery($request);

        $rows = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'invoice_date', 'desc')
            ->get()
            ->map(fn (SalesInvoice $i) => [
                'number' => $i->invoice_number,
                'client' => $i->client?->name,
                'date' => $i->invoice_date?->format('Y-m-d'),
                'due' => $i->due_date?->format('Y-m-d'),
                'total' => number_format($i->total, 2),
                'balance' => number_format($i->balance(), 2),
                'status' => ucfirst(str_replace('_', ' ', $i->status)),
            ]);

        return $this->streamCsvExport('sales-invoices-' . now()->format('Ymd_His') . '.csv', [
            'number' => 'Invoice Number', 'client' => 'Client', 'date' => 'Invoice Date', 'due' => 'Due Date',
            'total' => 'Total', 'balance' => 'Balance', 'status' => 'Status',
        ], $rows);
    }

    public function show(SalesInvoice $salesInvoice)
    {
        $salesInvoice->load(['client', 'items', 'creditNotes', 'paymentAllocations.payment']);

        return view('sales-invoices.show', compact('salesInvoice'));
    }

    public function pdf(Request $request, SalesInvoice $salesInvoice)
    {
        $salesInvoice->load(['client', 'items', 'salesOrder.branch']);

        $isDuplicate = $salesInvoice->print_count > 0;
        $salesInvoice->increment('print_count');

        $qrImage = QrCode::size(200)->generate(
            "INVOICE:{$salesInvoice->invoice_number}|TOTAL:{$salesInvoice->total}|CLIENT:{$salesInvoice->client?->name}"
        );
        $qrImage = 'data:image/svg+xml;base64,' . base64_encode($qrImage);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $salesInvoice,
            'isDuplicate' => $isDuplicate,
            'qrImage' => $qrImage,
        ])->setPaper('a4', 'portrait');

        $filename = "{$salesInvoice->invoice_number}.pdf";

        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
