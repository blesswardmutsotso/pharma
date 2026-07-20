<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SalesInvoiceController extends Controller
{
    public function index()
    {
        $invoices = SalesInvoice::with('client')->latest()->paginate(20);

        return view('sales-invoices.index', compact('invoices'));
    }

    public function show(SalesInvoice $salesInvoice)
    {
        $salesInvoice->load(['client', 'items', 'creditNotes', 'paymentAllocations.payment']);

        return view('sales-invoices.show', compact('salesInvoice'));
    }

    public function pdf(Request $request, SalesInvoice $salesInvoice)
    {
        $salesInvoice->load(['client', 'items', 'salesOrder']);

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
